<?php

namespace Tests\PostgreSQL\IdentityAccessAuthenticationAuthority;

use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\OwnerPersistenceState;
use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\PersistenceWriteResult;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\IdentityAccessCompletionPersistenceMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlSessionStore;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlSessionPolicyAuthorityTest extends TestCase
{
    private static PDO $connection;

    public static function setUpBeforeClass(): void
    {
        self::$connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate(self::$connection);
    }

    protected function setUp(): void
    {
        PostgreSqlTestEnvironment::reset(self::$connection);
    }

    public function test_policy_state_is_additive_replayable_and_optimistically_locked(): void
    {
        $store = new PostgreSqlSessionStore(self::$connection, new IdentityAccessCompletionPersistenceMapper);
        $state = $this->state(1, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', str_repeat('a', 64));

        self::assertSame(PersistenceWriteResult::Applied, $store->save($state, 0));
        self::assertSame(PersistenceWriteResult::IdempotentReplay, $store->save($state, 0));
        self::assertSame(PersistenceWriteResult::VersionConflict, $store->save($this->state(3, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', str_repeat('b', 64)), 1));
        $read = $store->read($state->identity);
        self::assertSame('session-policy-v1', $read->values['policy_version']);
        self::assertSame(
            '2026-08-10T08:00:00+00:00',
            (new DateTimeImmutable((string) $read->values['original_issued_at']))->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:sP'),
        );
    }

    private function state(int $version, string $intent, string $checksum): OwnerPersistenceState
    {
        return new OwnerPersistenceState('018f1f26-7cc2-7d7e-8d23-f76f0b3be222', $version, $intent, $checksum, [
            'account_id' => '018f1f26-7cc2-7d7e-8d23-f76f0b3be221',
            'secret_hash' => 'session-secret-hmac-sha256-v1.local-v1.'.str_repeat('a', 64),
            'state' => 'Active',
            'policy_version' => 'session-policy-v1',
            'original_issued_at' => '2026-08-10T08:00:00+00:00',
            'idle_expires_at' => '2026-08-10T08:30:00+00:00',
            'absolute_expires_at' => '2026-08-10T16:00:00+00:00',
            'issued_at' => '2026-08-10T08:00:00+00:00',
            'expires_at' => '2026-08-10T16:00:00+00:00',
            'last_seen_at' => '2026-08-10T08:00:00+00:00',
            'rotated_to' => null,
            'device_reference' => null,
            'issued_checkpoint' => 0,
        ]);
    }
}
