<?php

namespace Appart\Modules\SecurityCompliance\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\SecurityCompliance\Application\OwnerSource\ComplianceControlReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\ComplianceControlRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\ComplianceControlWriteResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\IncidentReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\IncidentRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\IncidentWriteResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\PrivacyPolicyReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\PrivacyPolicyRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\PrivacyPolicyWriteResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecretInventoryReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecretInventoryRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecretInventoryWriteResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityAuditReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityAuditRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityAuditWriteResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityComplianceOwnerSource;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;
use Appart\Modules\SecurityCompliance\Infrastructure\Persistence\SecurityComplianceOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

/** @phpstan-type OwnerRow array{subject_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string,revision_checksum:string} */
final readonly class PostgreSqlSecurityComplianceOwnerSource implements SecurityComplianceOwnerSource
{
    private const SAVEPOINT = 'security_compliance_owner_source';

    public function __construct(private PDO $connection, private SecurityComplianceOwnerSourceMapper $mapper) {}

    public function appendSecretInventory(SecretInventoryRevisionState $revision): SecretInventoryWriteResult
    {
        try {
            return SecretInventoryWriteResult::from($this->appendRow($this->mapper->secretInventoryToRow($revision)));
        } catch (PDOException) {
            return SecretInventoryWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return SecretInventoryWriteResult::Corrupted;
        }
    }

    public function appendSecurityAudit(SecurityAuditRevisionState $revision): SecurityAuditWriteResult
    {
        try {
            return SecurityAuditWriteResult::from($this->appendRow($this->mapper->securityAuditToRow($revision)));
        } catch (PDOException) {
            return SecurityAuditWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return SecurityAuditWriteResult::Corrupted;
        }
    }

    public function appendIncident(IncidentRevisionState $revision): IncidentWriteResult
    {
        try {
            return IncidentWriteResult::from($this->appendRow($this->mapper->incidentToRow($revision)));
        } catch (PDOException) {
            return IncidentWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return IncidentWriteResult::Corrupted;
        }
    }

    public function appendPrivacyPolicy(PrivacyPolicyRevisionState $revision): PrivacyPolicyWriteResult
    {
        try {
            return PrivacyPolicyWriteResult::from($this->appendRow($this->mapper->privacyPolicyToRow($revision)));
        } catch (PDOException) {
            return PrivacyPolicyWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return PrivacyPolicyWriteResult::Corrupted;
        }
    }

    public function appendComplianceControl(ComplianceControlRevisionState $revision): ComplianceControlWriteResult
    {
        try {
            return ComplianceControlWriteResult::from($this->appendRow($this->mapper->complianceControlToRow($revision)));
        } catch (PDOException) {
            return ComplianceControlWriteResult::DependencyUnavailable;
        } catch (Throwable) {
            return ComplianceControlWriteResult::Corrupted;
        }
    }

    public function readSecretInventory(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): SecretInventoryReadResult
    {
        try {
            $row = $this->temporalRow($subject->value, 'secret_inventory', $observedAt->canonical());

            return $row === false ? SecretInventoryReadResult::missing() : SecretInventoryReadResult::found($this->mapper->toSecretInventoryState($row));
        } catch (PDOException) {
            return SecretInventoryReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return SecretInventoryReadResult::corrupted();
        }
    }

    public function readSecurityAudit(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): SecurityAuditReadResult
    {
        try {
            $row = $this->temporalRow($subject->value, 'security_audit', $observedAt->canonical());

            return $row === false ? SecurityAuditReadResult::missing() : SecurityAuditReadResult::found($this->mapper->toSecurityAuditState($row));
        } catch (PDOException) {
            return SecurityAuditReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return SecurityAuditReadResult::corrupted();
        }
    }

    public function readIncident(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): IncidentReadResult
    {
        try {
            $row = $this->temporalRow($subject->value, 'incident', $observedAt->canonical());

            return $row === false ? IncidentReadResult::missing() : IncidentReadResult::found($this->mapper->toIncidentState($row));
        } catch (PDOException) {
            return IncidentReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return IncidentReadResult::corrupted();
        }
    }

    public function readPrivacyPolicy(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): PrivacyPolicyReadResult
    {
        try {
            $row = $this->temporalRow($subject->value, 'privacy_policy', $observedAt->canonical());

            return $row === false ? PrivacyPolicyReadResult::missing() : PrivacyPolicyReadResult::found($this->mapper->toPrivacyPolicyState($row));
        } catch (PDOException) {
            return PrivacyPolicyReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return PrivacyPolicyReadResult::corrupted();
        }
    }

    public function readComplianceControl(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): ComplianceControlReadResult
    {
        try {
            $row = $this->temporalRow($subject->value, 'compliance_control', $observedAt->canonical());

            return $row === false ? ComplianceControlReadResult::missing() : ComplianceControlReadResult::found($this->mapper->toComplianceControlState($row));
        } catch (PDOException) {
            return ComplianceControlReadResult::dependencyUnavailable();
        } catch (Throwable) {
            return ComplianceControlReadResult::corrupted();
        }
    }

    /** @param OwnerRow $row */
    private function appendRow(array $row): string
    {
        return $this->transactional(function () use ($row): string {
            $this->lockStream($row['subject_key'], $row['stream_type']);
            $current = $this->currentRow($row['subject_key'], $row['stream_type']);
            if ($current === false) {
                if ($row['revision'] !== 1) {
                    return 'version_conflict';
                }
            } else {
                $currentRevision = $current['revision'];
                if ($row['revision'] <= $currentRevision) {
                    return $this->classifyExisting($row);
                }
                if ($row['revision'] !== $currentRevision + 1 || new DateTimeImmutable($row['effective_at']) <= new DateTimeImmutable($current['effective_at']) || new DateTimeImmutable($row['recorded_at']) < new DateTimeImmutable($current['recorded_at'])) {
                    return 'version_conflict';
                }
            }
            $result = $this->insertOrClassify($row);
            if ($result === 'applied') {
                $this->updateIndex($row);
            }

            return $result;
        });
    }

    /** @return OwnerRow|false */
    private function temporalRow(string $key, string $stream, string $at): array|false
    {
        $query = $this->connection->prepare($this->selectSql().' WHERE subject_key=:key AND stream_type=:stream AND effective_at<=CAST(:at AS timestamptz) AND recorded_at<=CAST(:at AS timestamptz) ORDER BY effective_at DESC,recorded_at DESC,revision DESC LIMIT 1');
        $query->execute(['key' => $key, 'stream' => $stream, 'at' => $at]);

        /** @var OwnerRow|false */ return $query->fetch(PDO::FETCH_ASSOC);
    }

    private function transactional(callable $operation): string
    {
        $ownsTransaction = ! $this->connection->inTransaction();
        $ownsTransaction ? $this->connection->beginTransaction() : $this->connection->exec('SAVEPOINT '.self::SAVEPOINT);
        try {
            $result = $operation();
            $ownsTransaction ? $this->connection->commit() : $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);

            return $result;
        } catch (Throwable $exception) {
            if ($ownsTransaction) {
                $this->connection->rollBack();
            } else {
                $this->connection->exec('ROLLBACK TO SAVEPOINT '.self::SAVEPOINT);
                $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);
            }
            throw $exception;
        }
    }

    private function lockStream(string $key, string $stream): void
    {
        $query = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:key,0))');
        $query->execute(['key' => $key."\n".$stream]);
    }

    /** @param OwnerRow $row */
    private function insertOrClassify(array $row): string
    {
        $query = $this->connection->prepare('INSERT INTO security_compliance.owner_revision_journal(subject_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum) VALUES(:subject_key,:stream_type,:revision,:decision,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:revision_checksum) ON CONFLICT(subject_key,stream_type,revision) DO NOTHING');
        $query->execute($row);

        return $query->rowCount() === 1 ? 'applied' : $this->classifyExisting($row);
    }

    /** @param OwnerRow $row */
    private function updateIndex(array $row): void
    {
        $query = $this->connection->prepare('INSERT INTO security_compliance.owner_current_index(subject_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum) VALUES(:subject_key,:stream_type,:revision,:decision,CAST(:effective_at AS timestamptz),CAST(:recorded_at AS timestamptz),:revision_checksum) ON CONFLICT(subject_key,stream_type) DO UPDATE SET revision=EXCLUDED.revision,decision=EXCLUDED.decision,effective_at=EXCLUDED.effective_at,recorded_at=EXCLUDED.recorded_at,revision_checksum=EXCLUDED.revision_checksum WHERE EXCLUDED.revision>owner_current_index.revision');
        $query->execute($row);
        if ($query->rowCount() !== 1) {
            throw new RuntimeException('SecurityCompliance current index did not converge.');
        }
    }

    /** @param OwnerRow $row */
    private function classifyExisting(array $row): string
    {
        $query = $this->connection->prepare('SELECT revision_checksum FROM security_compliance.owner_revision_journal WHERE subject_key=:subject_key AND stream_type=:stream_type AND revision=:revision');
        $query->execute(['subject_key' => $row['subject_key'], 'stream_type' => $row['stream_type'], 'revision' => $row['revision']]);
        $checksum = $query->fetchColumn();
        if (! is_string($checksum)) {
            return 'version_conflict';
        }

        return hash_equals($checksum, $row['revision_checksum']) ? 'already_applied' : 'divergent_revision';
    }

    /** @return OwnerRow|false */
    private function currentRow(string $key, string $stream): array|false
    {
        $query = $this->connection->prepare($this->selectSql().' WHERE subject_key=:key AND stream_type=:stream ORDER BY revision DESC LIMIT 1 FOR UPDATE');
        $query->execute(['key' => $key, 'stream' => $stream]);

        /** @var OwnerRow|false */ return $query->fetch(PDO::FETCH_ASSOC);
    }

    private function selectSql(): string
    {
        return 'SELECT subject_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum FROM security_compliance.owner_revision_journal';
    }
}
