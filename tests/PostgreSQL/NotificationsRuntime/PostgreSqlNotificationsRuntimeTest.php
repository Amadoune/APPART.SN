<?php

namespace Tests\PostgreSQL\NotificationsRuntime;

use Appart\Modules\Notifications\Application\Runtime\DeterministicNotificationsRuntime;
use Appart\Modules\Notifications\Application\Runtime\DeterministicNotificationsRuntimeAvailabilityPolicy;
use Appart\Modules\Notifications\Application\Runtime\NotificationsRuntimeAvailability;
use Appart\Modules\Notifications\Infrastructure\Persistence\NotificationsOwnerSourceMapper;
use Appart\Modules\Notifications\Infrastructure\Persistence\PostgreSql\PostgreSqlNotificationsOwnerSource;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlNotificationsRuntimeTest extends TestCase
{
    public function test_runtime_reports_real_owner_source_availability_without_business_decision(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        $migration = dirname(__DIR__, 3).'/src/Modules/Notifications/Infrastructure/Persistence/PostgreSql/Migrations/079_notifications_owner_source.sql';
        $connection->exec((string) file_get_contents($migration));
        $source = new PostgreSqlNotificationsOwnerSource($connection, new NotificationsOwnerSourceMapper);
        $runtime = new DeterministicNotificationsRuntime(new DeterministicNotificationsRuntimeAvailabilityPolicy($source));

        self::assertSame(NotificationsRuntimeAvailability::Available, $runtime->availability());
        self::assertSame(NotificationsRuntimeAvailability::Available, $runtime->diagnostics()->availability);
    }
}
