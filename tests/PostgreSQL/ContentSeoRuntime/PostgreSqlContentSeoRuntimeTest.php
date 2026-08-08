<?php

namespace Tests\PostgreSQL\ContentSeoRuntime;

use Appart\Modules\ContentSeo\Application\Runtime\ContentSeoRuntimeAvailability;
use Appart\Modules\ContentSeo\Application\Runtime\DeterministicContentSeoRuntime;
use Appart\Modules\ContentSeo\Application\Runtime\DeterministicContentSeoRuntimeAvailabilityPolicy;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\ContentSeoOwnerSourceMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlContentSeoOwnerSource;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlContentSeoRuntimeTest extends TestCase
{
    public function test_runtime_reports_real_owner_source_availability_without_business_decision(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        $migration = dirname(__DIR__, 3).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/078_editorial_content_operational_seo_owner_source.sql';
        $connection->exec((string) file_get_contents($migration));
        $source = new PostgreSqlContentSeoOwnerSource($connection, new ContentSeoOwnerSourceMapper);
        $runtime = new DeterministicContentSeoRuntime(new DeterministicContentSeoRuntimeAvailabilityPolicy($source));

        self::assertSame(ContentSeoRuntimeAvailability::Available, $runtime->availability());
        self::assertSame(ContentSeoRuntimeAvailability::Available, $runtime->diagnostics()->availability);
    }
}
