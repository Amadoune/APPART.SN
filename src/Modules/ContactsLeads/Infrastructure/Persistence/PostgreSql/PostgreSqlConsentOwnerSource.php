<?php

namespace Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionReadResult;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionState;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionWriteResult;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\Contract\ConsentOwnerSource;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\ConsentOwnerSourceMapper;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlConsentOwnerSource implements ConsentOwnerSource
{
    private const SAVEPOINT = 'contacts_leads_consent_source';

    public function __construct(
        private PDO $connection,
        private ConsentOwnerSourceMapper $mapper,
    ) {}

    public function append(ConsentRevisionState $revision): ConsentRevisionWriteResult
    {
        try {
            return $this->transactional(function () use ($revision): ConsentRevisionWriteResult {
                $row = $this->mapper->toRow($revision);
                $this->lockStream($revision->intentId);
                $current = $this->currentRow($revision->intentId, true);

                if ($current === false) {
                    if ($revision->revision !== 1) {
                        return ConsentRevisionWriteResult::VersionConflict;
                    }

                    return $this->insertOrClassify($row);
                }

                $currentVersion = (int) $current['revision'];
                if ($revision->revision <= $currentVersion) {
                    return $this->classifyExisting($row);
                }
                if ($revision->revision !== $currentVersion + 1) {
                    return ConsentRevisionWriteResult::VersionConflict;
                }
                if (new DateTimeImmutable($row['effective_at']) <= new DateTimeImmutable((string) $current['effective_at'])
                    || new DateTimeImmutable($row['recorded_at']) < new DateTimeImmutable((string) $current['recorded_at'])) {
                    return ConsentRevisionWriteResult::VersionConflict;
                }

                return $this->insertOrClassify($row);
            });
        } catch (Throwable) {
            return ConsentRevisionWriteResult::DependencyUnavailable;
        }
    }

    public function at(LeadIngressIntentId $intentId, DateTimeImmutable $observedAt): ConsentRevisionReadResult
    {
        try {
            $history = $this->history($intentId);
            if ($history === []) {
                return ConsentRevisionReadResult::missing($intentId);
            }
            $observedAt = $observedAt->setTimezone(new DateTimeZone('UTC'));
            $applicable = null;
            foreach ($history as $revision) {
                if ($revision->effectiveAt <= $observedAt) {
                    $applicable = $revision;
                }
            }

            return $applicable === null
                ? ConsentRevisionReadResult::missing($intentId)
                : ConsentRevisionReadResult::found($applicable);
        } catch (PDOException) {
            return ConsentRevisionReadResult::dependencyUnavailable($intentId);
        } catch (Throwable) {
            return ConsentRevisionReadResult::corrupted($intentId);
        }
    }

    public function history(LeadIngressIntentId $intentId): array
    {
        $statement = $this->connection->prepare($this->selectSql().' WHERE lead_ingress_intent_id=:intent_id ORDER BY revision ASC');
        $statement->execute(['intent_id' => $intentId->value]);
        $states = [];
        $expectedRevision = 1;
        $previousEffectiveAt = null;
        $previousRecordedAt = null;
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $state = $this->mapper->toState($row);
            if ($state->revision !== $expectedRevision
                || ($previousEffectiveAt !== null && $state->effectiveAt <= $previousEffectiveAt)
                || ($previousRecordedAt !== null && $state->recordedAt < $previousRecordedAt)) {
                throw new \RuntimeException('Corrupted consent revision chronology.');
            }
            $states[] = $state;
            $expectedRevision++;
            $previousEffectiveAt = $state->effectiveAt;
            $previousRecordedAt = $state->recordedAt;
        }

        return $states;
    }

    /** @param callable(): ConsentRevisionWriteResult $operation */
    private function transactional(callable $operation): ConsentRevisionWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        } else {
            $this->connection->exec('SAVEPOINT '.self::SAVEPOINT);
        }
        try {
            $result = $operation();
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            } else {
                $this->connection->exec('ROLLBACK TO SAVEPOINT '.self::SAVEPOINT);
                $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);
            }
            throw $error;
        }
        if ($owner) {
            $this->connection->commit();
        } else {
            $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);
        }

        return $result;
    }

    /** @param array{intent_id:string,revision:int,decision:string,effective_at:string,recorded_at:string,policy_reference:?string,checksum:string} $row */
    private function insertOrClassify(array $row): ConsentRevisionWriteResult
    {
        $statement = $this->connection->prepare(
            'INSERT INTO contacts_leads.consent_decision_revisions
             (lead_ingress_intent_id,revision,decision,effective_at,recorded_at,policy_reference,revision_checksum)
             VALUES(CAST(:intent_id AS uuid),:revision,:decision,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:policy_reference,:checksum)
             ON CONFLICT (lead_ingress_intent_id,revision) DO NOTHING',
        );
        $statement->execute($row);

        return $statement->rowCount() === 1
            ? ConsentRevisionWriteResult::Applied
            : $this->classifyExisting($row);
    }

    /** @param array{intent_id:string,revision:int,decision:string,effective_at:string,recorded_at:string,policy_reference:?string,checksum:string} $row */
    private function classifyExisting(array $row): ConsentRevisionWriteResult
    {
        $statement = $this->connection->prepare(
            'SELECT revision_checksum FROM contacts_leads.consent_decision_revisions
             WHERE lead_ingress_intent_id=:intent_id AND revision=:revision',
        );
        $statement->execute(['intent_id' => $row['intent_id'], 'revision' => $row['revision']]);
        $checksum = $statement->fetchColumn();
        if (! is_string($checksum)) {
            return ConsentRevisionWriteResult::Corrupted;
        }

        return hash_equals($checksum, $row['checksum'])
            ? ConsentRevisionWriteResult::AlreadyApplied
            : ConsentRevisionWriteResult::DivergentRevision;
    }

    /** @return array<string, mixed>|false */
    private function currentRow(LeadIngressIntentId $intentId, bool $forUpdate): array|false
    {
        $sql = $this->selectSql().' WHERE lead_ingress_intent_id=:intent_id ORDER BY revision DESC LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }
        $statement = $this->connection->prepare($sql);
        $statement->execute(['intent_id' => $intentId->value]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    private function lockStream(LeadIngressIntentId $intentId): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:intent_id,0))');
        $statement->execute(['intent_id' => $intentId->value]);
    }

    private function selectSql(): string
    {
        return 'SELECT lead_ingress_intent_id::text,revision,decision,effective_at,recorded_at,policy_reference,revision_checksum
                FROM contacts_leads.consent_decision_revisions';
    }
}
