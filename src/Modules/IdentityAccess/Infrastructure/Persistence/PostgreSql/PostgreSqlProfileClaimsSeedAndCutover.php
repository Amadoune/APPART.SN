<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\OwnerPersistenceState;
use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\PersistenceWriteResult;
use Appart\Modules\IdentityAccess\Application\ProfileClaimsCutover\Contract\ProfileClaimsSeedProtector;
use Appart\Modules\IdentityAccess\Application\ProfileClaimsCutover\ProfileClaimsCutoverResult;
use Appart\Modules\IdentityAccess\Application\ProfileClaimsCutover\ProfileClaimsCutoverStatus;
use Appart\Modules\IdentityAccess\Application\ProfileClaimsCutover\ProfileClaimsSeedNormalizer;
use Appart\Modules\IdentityAccess\Application\ProfileClaimsCutover\ProfileClaimsSeedSourceState;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\IdentityAccessCompletionPersistenceMapper;
use DateTimeImmutable;
use PDO;
use Throwable;

final readonly class PostgreSqlProfileClaimsSeedAndCutover
{
    private PostgreSqlUserProfileStore $profiles;

    private PostgreSqlIdentityClaimStore $claims;

    public function __construct(
        private PDO $connection,
        private ProfileClaimsSeedNormalizer $normalizer,
        IdentityAccessCompletionPersistenceMapper $mapper,
    ) {
        $this->profiles = new PostgreSqlUserProfileStore($connection, $mapper);
        $this->claims = new PostgreSqlIdentityClaimStore($connection, $mapper);
    }

    /**
     * @param  list<ProfileClaimsSeedSourceState>  $sources
     */
    public function seedAndCutOver(
        string $runId,
        array $sources,
        ProfileClaimsSeedProtector $protector,
        DateTimeImmutable $occurredAt,
    ): ProfileClaimsCutoverResult {
        $plan = $this->plan($sources, $protector);
        $checksum = hash('sha256', json_encode($plan, JSON_THROW_ON_ERROR));
        $existing = $this->run($runId);
        if ($existing !== false) {
            if ($existing['source_checksum'] === $checksum && $existing['state'] === 'Committed') {
                return new ProfileClaimsCutoverResult(
                    ProfileClaimsCutoverStatus::IdempotentReplay,
                    (int) $existing['profile_count'],
                    (int) $existing['claim_count'],
                );
            }

            return new ProfileClaimsCutoverResult(ProfileClaimsCutoverStatus::PersistenceRejected, 0, 0);
        }
        if ($this->authority() === 'Profile') {
            return new ProfileClaimsCutoverResult(ProfileClaimsCutoverStatus::AlreadyCutOver, 0, 0);
        }

        $divergences = $this->divergences($plan);
        if ($divergences !== []) {
            $this->quarantine($runId, count($sources), $checksum, $divergences, $occurredAt);

            return new ProfileClaimsCutoverResult(
                ProfileClaimsCutoverStatus::Quarantined,
                0,
                0,
                array_values(array_unique(array_column($divergences, 'code'))),
            );
        }

        $this->connection->beginTransaction();
        try {
            $this->insertRun($runId, count($sources), $checksum, 'Prepared', $occurredAt);
            foreach ($plan as $item) {
                $this->persist($runId, $item, $occurredAt);
            }
            $this->activateAuthority($runId, $checksum, $occurredAt);
            $statement = $this->connection->prepare(
                "UPDATE identity_access_completion.profile_claim_seed_runs
                 SET state='Committed',profile_count=:profiles,claim_count=:claims,completed_at=:completed_at
                 WHERE run_id=CAST(:run_id AS uuid) AND state='Prepared'",
            );
            $statement->execute([
                'profiles' => count($plan),
                'claims' => count($plan) * 2,
                'completed_at' => $this->date($occurredAt),
                'run_id' => $runId,
            ]);
            $this->connection->commit();

            return new ProfileClaimsCutoverResult(ProfileClaimsCutoverStatus::Committed, count($plan), count($plan) * 2);
        } catch (Throwable) {
            $this->connection->rollBack();

            return new ProfileClaimsCutoverResult(ProfileClaimsCutoverStatus::PersistenceRejected, 0, 0);
        }
    }

    public function rollback(string $runId, string $intentId, DateTimeImmutable $occurredAt): ProfileClaimsCutoverResult
    {
        if ($this->activeRun() !== $runId) {
            return new ProfileClaimsCutoverResult(ProfileClaimsCutoverStatus::RollbackRejected, 0, 0);
        }
        $this->connection->beginTransaction();
        try {
            $statement = $this->connection->prepare(
                'SELECT account_id::text,email_claim_id::text,phone_claim_id::text,profile_version
                 FROM identity_access_completion.profile_claim_seed_manifest
                 WHERE run_id=CAST(:run_id AS uuid) FOR UPDATE',
            );
            $statement->execute(['run_id' => $runId]);
            $manifest = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($manifest as $row) {
                $deletedClaims = $this->deleteSeededClaims((string) $row['email_claim_id'], (string) $row['phone_claim_id']);
                $deletedProfile = $this->deleteSeededProfile((string) $row['account_id'], (int) $row['profile_version']);
                if ($deletedClaims !== 2 || $deletedProfile !== 1) {
                    throw new \RuntimeException('Seeded owner state changed after cutover.');
                }
            }
            $checksum = hash('sha256', 'rollback:'.$runId);
            $authority = $this->connection->prepare(
                "UPDATE identity_access_completion.profile_claim_authority
                 SET authority='Historical',generation=generation+1,active_run_id=NULL,changed_at=:changed_at,
                     last_intent_id=CAST(:intent_id AS uuid),last_intent_checksum=:checksum
                 WHERE authority_key='ProfileClaims' AND authority='Profile' AND active_run_id=CAST(:run_id AS uuid)",
            );
            $authority->execute([
                'changed_at' => $this->date($occurredAt),
                'intent_id' => $intentId,
                'checksum' => $checksum,
                'run_id' => $runId,
            ]);
            if ($authority->rowCount() !== 1) {
                throw new \RuntimeException('Authority rollback conflict.');
            }
            $run = $this->connection->prepare(
                "UPDATE identity_access_completion.profile_claim_seed_runs
                 SET state='RolledBack',completed_at=:completed_at
                 WHERE run_id=CAST(:run_id AS uuid) AND state='Committed'",
            );
            $run->execute(['completed_at' => $this->date($occurredAt), 'run_id' => $runId]);
            $this->connection->commit();

            return new ProfileClaimsCutoverResult(
                ProfileClaimsCutoverStatus::RolledBack,
                count($manifest),
                count($manifest) * 2,
            );
        } catch (Throwable) {
            $this->connection->rollBack();

            return new ProfileClaimsCutoverResult(ProfileClaimsCutoverStatus::RollbackRejected, 0, 0);
        }
    }

    /**
     * @param  list<ProfileClaimsSeedSourceState>  $sources
     * @return list<array<string, int|string>>
     */
    private function plan(array $sources, ProfileClaimsSeedProtector $protector): array
    {
        usort($sources, static fn (ProfileClaimsSeedSourceState $a, ProfileClaimsSeedSourceState $b): int => $a->accountId <=> $b->accountId);
        $plan = [];
        foreach ($sources as $source) {
            $accountId = $source->accountId;
            $email = $this->normalizer->email($source->email);
            $phone = $this->normalizer->phone($source->phone);
            $plan[] = [
                'account_id' => $accountId,
                'historical_version' => $source->historicalVersion,
                'display_name' => $protector->protect($this->normalizer->name($source->name)),
                'email' => $protector->protect($email),
                'email_fingerprint' => $protector->fingerprint($email),
                'phone' => $protector->protect($phone),
                'phone_fingerprint' => $protector->fingerprint($phone),
                'email_claim_id' => $this->uuid($accountId.':Email'),
                'phone_claim_id' => $this->uuid($accountId.':Phone'),
            ];
        }

        return $plan;
    }

    /**
     * @param  list<array<string, int|string>>  $plan
     * @return list<array{account_id: string, code: string, evidence: string}>
     */
    private function divergences(array $plan): array
    {
        $seen = [];
        $result = [];
        foreach ($plan as $item) {
            foreach (['email_fingerprint', 'phone_fingerprint'] as $field) {
                $key = $field.':'.$item[$field];
                if (isset($seen[$key])) {
                    $result[] = ['account_id' => (string) $item['account_id'], 'code' => 'DuplicateHistoricalClaim', 'evidence' => hash('sha256', $key)];
                }
                $seen[$key] = true;
                $statement = $this->connection->prepare(
                    'SELECT account_id::text FROM identity_access_completion.identity_claims
                     WHERE claim_type=:type AND claim_fingerprint=:fingerprint',
                );
                $statement->execute([
                    'type' => $field === 'email_fingerprint' ? 'Email' : 'Phone',
                    'fingerprint' => $item[$field],
                ]);
                $owner = $statement->fetchColumn();
                if (is_string($owner) && $owner !== $item['account_id']) {
                    $result[] = ['account_id' => (string) $item['account_id'], 'code' => 'ClaimCollision', 'evidence' => hash('sha256', $key)];
                }
            }
            if ($this->profiles->read((string) $item['account_id']) !== null) {
                $result[] = ['account_id' => (string) $item['account_id'], 'code' => 'ProfileConflict', 'evidence' => hash('sha256', (string) $item['account_id'])];
            }
        }

        return $result;
    }

    /** @param array<string, int|string> $item */
    private function persist(string $runId, array $item, DateTimeImmutable $occurredAt): void
    {
        $accountId = (string) $item['account_id'];
        $at = $this->date($occurredAt);
        $profileChecksum = hash('sha256', json_encode($item, JSON_THROW_ON_ERROR));
        $profile = new OwnerPersistenceState(
            $accountId, 1, $this->uuid($runId.':'.$accountId.':profile'), $profileChecksum,
            [
                'display_name_ciphertext' => (string) $item['display_name'],
                'email_ciphertext' => (string) $item['email'],
                'email_fingerprint' => (string) $item['email_fingerprint'],
                'phone_ciphertext' => (string) $item['phone'],
                'phone_fingerprint' => (string) $item['phone_fingerprint'],
                'normalization_version' => ProfileClaimsSeedNormalizer::VERSION,
                'enrolled_at' => $at,
                'updated_at' => $at,
            ],
        );
        if ($this->profiles->save($profile, 0) !== PersistenceWriteResult::Applied) {
            throw new \RuntimeException('Profile seed rejected.');
        }
        foreach (['Email' => 'email', 'Phone' => 'phone'] as $type => $prefix) {
            $claimId = (string) $item[$prefix.'_claim_id'];
            $claim = new OwnerPersistenceState(
                $claimId, 1, $this->uuid($runId.':'.$claimId), hash('sha256', $profileChecksum.':'.$type),
                [
                    'account_id' => $accountId,
                    'claim_type' => $type,
                    'claim_ciphertext' => (string) $item[$prefix],
                    'claim_fingerprint' => (string) $item[$prefix.'_fingerprint'],
                    'normalization_version' => ProfileClaimsSeedNormalizer::VERSION,
                    'state' => 'Active',
                    'reserved_at' => $at,
                    'activated_at' => $at,
                    'ended_at' => null,
                    'contact_change_id' => null,
                ],
            );
            if ($this->claims->save($claim, 0) !== PersistenceWriteResult::Applied) {
                throw new \RuntimeException('Claim seed rejected.');
            }
        }
        $manifest = $this->connection->prepare(
            'INSERT INTO identity_access_completion.profile_claim_seed_manifest
             (run_id,account_id,profile_version,email_claim_id,phone_claim_id,row_checksum)
             VALUES(CAST(:run_id AS uuid),CAST(:account_id AS uuid),1,CAST(:email_claim_id AS uuid),CAST(:phone_claim_id AS uuid),:checksum)',
        );
        $manifest->execute([
            'run_id' => $runId,
            'account_id' => $accountId,
            'email_claim_id' => $item['email_claim_id'],
            'phone_claim_id' => $item['phone_claim_id'],
            'checksum' => $profileChecksum,
        ]);
    }

    /**
     * @param  list<array{account_id: string, code: string, evidence: string}>  $divergences
     */
    private function quarantine(string $runId, int $sources, string $checksum, array $divergences, DateTimeImmutable $at): void
    {
        $this->connection->beginTransaction();
        try {
            $this->insertRun($runId, $sources, $checksum, 'Quarantined', $at, count($divergences));
            $statement = $this->connection->prepare(
                'INSERT INTO identity_access_completion.profile_claim_seed_quarantine
                 (run_id,account_id,divergence_code,evidence_checksum,detected_at)
                 VALUES(CAST(:run_id AS uuid),CAST(:account_id AS uuid),:code,:evidence,:detected_at)
                 ON CONFLICT DO NOTHING',
            );
            foreach ($divergences as $divergence) {
                $statement->execute([
                    'run_id' => $runId,
                    'account_id' => $divergence['account_id'],
                    'code' => $divergence['code'],
                    'evidence' => $divergence['evidence'],
                    'detected_at' => $this->date($at),
                ]);
            }
            $this->connection->commit();
        } catch (Throwable $error) {
            $this->connection->rollBack();
            throw $error;
        }
    }

    private function insertRun(string $runId, int $sources, string $checksum, string $state, DateTimeImmutable $at, int $divergences = 0): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO identity_access_completion.profile_claim_seed_runs
             (run_id,source_snapshot_version,normalization_version,state,source_count,profile_count,claim_count,
              divergence_count,source_checksum,started_at,completed_at)
             VALUES(CAST(:run_id AS uuid),1,:normalization_version,:state,:source_count,0,0,:divergences,:checksum,:started_at,:completed_at)',
        );
        $statement->execute([
            'run_id' => $runId,
            'normalization_version' => ProfileClaimsSeedNormalizer::VERSION,
            'state' => $state,
            'source_count' => $sources,
            'divergences' => $divergences,
            'checksum' => $checksum,
            'started_at' => $this->date($at),
            'completed_at' => $state === 'Prepared' ? null : $this->date($at),
        ]);
    }

    private function activateAuthority(string $runId, string $checksum, DateTimeImmutable $at): void
    {
        $statement = $this->connection->prepare(
            "UPDATE identity_access_completion.profile_claim_authority
             SET authority='Profile',generation=generation+1,active_run_id=CAST(:run_id AS uuid),
                 changed_at=:changed_at,last_intent_id=CAST(:run_id AS uuid),last_intent_checksum=:checksum
             WHERE authority_key='ProfileClaims' AND authority='Historical'",
        );
        $statement->execute(['run_id' => $runId, 'changed_at' => $this->date($at), 'checksum' => $checksum]);
        if ($statement->rowCount() !== 1) {
            throw new \RuntimeException('Authority cutover conflict.');
        }
    }

    /** @return array<string, mixed>|false */
    private function run(string $runId): array|false
    {
        $statement = $this->connection->prepare(
            'SELECT state,profile_count,claim_count,source_checksum
             FROM identity_access_completion.profile_claim_seed_runs WHERE run_id=CAST(:run_id AS uuid)',
        );
        $statement->execute(['run_id' => $runId]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    private function authority(): string
    {
        return (string) $this->connection->query(
            "SELECT authority FROM identity_access_completion.profile_claim_authority WHERE authority_key='ProfileClaims'",
        )->fetchColumn();
    }

    private function activeRun(): ?string
    {
        $value = $this->connection->query(
            "SELECT active_run_id::text FROM identity_access_completion.profile_claim_authority WHERE authority_key='ProfileClaims'",
        )->fetchColumn();

        return is_string($value) ? $value : null;
    }

    private function deleteSeededClaims(string $emailClaimId, string $phoneClaimId): int
    {
        $statement = $this->connection->prepare(
            'DELETE FROM identity_access_completion.identity_claims
             WHERE claim_id IN (CAST(:email AS uuid),CAST(:phone AS uuid)) AND version=1',
        );
        $statement->execute(['email' => $emailClaimId, 'phone' => $phoneClaimId]);

        return $statement->rowCount();
    }

    private function deleteSeededProfile(string $accountId, int $version): int
    {
        $statement = $this->connection->prepare(
            'DELETE FROM identity_access_completion.user_profiles
             WHERE account_id=CAST(:account_id AS uuid) AND version=:version',
        );
        $statement->execute(['account_id' => $accountId, 'version' => $version]);

        return $statement->rowCount();
    }

    private function date(DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d\TH:i:s.uP');
    }

    private function uuid(string $name): string
    {
        $hash = sha1('6ba7b8109dad11d180b400c04fd430c8'.$name);

        return sprintf(
            '%s-%s-5%s-%s%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 13, 3),
            dechex((hexdec($hash[16]) & 0x3) | 0x8),
            substr($hash, 17, 3),
            substr($hash, 20, 12),
        );
    }
}
