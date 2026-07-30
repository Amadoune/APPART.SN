<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProfileClaimsSeedCutoverArchitectureTest extends TestCase
{
    #[Test]
    public function cutover_reads_snapshot_v1_and_never_writes_historical_tables(): void
    {
        $file = dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/PostgreSqlProfileClaimsSeedAndCutover.php';
        $source = (string) file_get_contents($file);

        self::assertStringContainsString('ProfileClaimsSeedSourceState', $source);
        self::assertDoesNotMatchRegularExpression(
            '/(?:INSERT\s+INTO|UPDATE|DELETE\s+FROM)\s+identity_access\./i',
            $source,
        );
        self::assertStringNotContainsString('AccountRegistry', $source);
        self::assertStringNotContainsString('\\Runtime\\', $source);
        self::assertStringNotContainsString('Outbox', $source);
        self::assertStringNotContainsString('Illuminate\\', $source);

        $adapter = (string) file_get_contents(
            dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Infrastructure/Persistence/HistoricalAccount/HistoricalAccountProfileClaimsSeedMapper.php',
        );
        self::assertStringContainsString('HistoricalAccountPersistenceSnapshotV1', $adapter);
        self::assertStringContainsString('ProfileClaimsSeedSourceState', $adapter);
    }

    #[Test]
    public function migration_052_is_additive_and_has_no_foreign_key_or_cascade(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/';
        $up = (string) file_get_contents($directory.'052_profile_claims_authority_cutover.sql');
        $down = (string) file_get_contents($directory.'052_profile_claims_authority_cutover.down.sql');

        self::assertStringContainsString('identity_access_completion.profile_claim_authority', $up);
        self::assertStringNotContainsString('REFERENCES ', strtoupper($up));
        self::assertStringNotContainsString('CASCADE', strtoupper($up));
        self::assertStringNotContainsString('ALTER TABLE', strtoupper($up));
        self::assertStringNotContainsString('identity_access.', $up);
        self::assertStringNotContainsString('identity_access.', $down);
    }
}
