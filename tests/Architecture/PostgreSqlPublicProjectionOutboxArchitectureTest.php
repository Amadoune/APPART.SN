<?php

namespace Tests\Architecture;

use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxParticipantTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

final class PostgreSqlPublicProjectionOutboxArchitectureTest extends TestCase
{
    public function test_outbox_infrastructure_has_no_laravel_or_runtime_delivery_component(): void
    {
        foreach ($this->phpFiles() as $path) {
            $contents = file_get_contents($path);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/(?:Illuminate\\|Laravel\\|ServiceProvider|Controller|Worker|Dispatcher|Relay|Queue|Job|Listener|PublicListingProjectionUpdater)/i', $contents, $path);
        }
    }

    public function test_mapper_and_writer_contain_no_domain_policy_or_aggregate_dependency(): void
    {
        foreach ([PostgreSqlPublicProjectionOutboxMapper::class, PostgreSqlPublicProjectionOutboxWriter::class] as $class) {
            $contents = file_get_contents((new ReflectionClass($class))->getFileName());
            self::assertIsString($contents);
            self::assertStringNotContainsString('\\Domain\\Policy\\', $contents);
            self::assertStringNotContainsString('\\Domain\\Model\\', $contents);
            self::assertStringNotContainsString('\\Domain\\Event\\', $contents);
        }
    }

    public function test_certified_repositories_do_not_know_the_outbox(): void
    {
        foreach ($this->repositoryFiles() as $path) {
            $contents = file_get_contents($path);
            self::assertIsString($contents);
            self::assertStringNotContainsString('PublicProjectionOutbox', $contents, $path);
            self::assertStringNotContainsString('outbox', strtolower($contents), $path);
        }
    }

    public function test_participant_uses_existing_transaction_contracts_without_repository_change(): void
    {
        $interfaces = (new ReflectionClass(PostgreSqlAggregateOutboxParticipantTransaction::class))->getInterfaceNames();
        sort($interfaces);

        self::assertSame([
            'Appart\\Modules\\AdministrationAudit\\Infrastructure\\Persistence\\AdministrativeActionTransaction',
            'Appart\\Modules\\ListingLifecycle\\Infrastructure\\Persistence\\ListingTransaction',
            'Appart\\Modules\\Media\\Infrastructure\\Persistence\\MediaCollectionTransaction',
            'Appart\\Modules\\RealEstateCatalog\\Infrastructure\\Persistence\\PropertyTransaction',
        ], $interfaces);
    }

    /** @return list<string> */
    private function phpFiles(): array
    {
        $root = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Infrastructure'.DIRECTORY_SEPARATOR.'PublicProjectionOutbox';
        $paths = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $paths[] = $file->getPathname();
            }
        }

        return $paths;
    }

    /** @return list<string> */
    private function repositoryFiles(): array
    {
        $root = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Modules';

        return [
            $root.'/ListingLifecycle/Infrastructure/Persistence/PostgreSql/PostgreSqlListingRepository.php',
            $root.'/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/PostgreSqlPropertyRepository.php',
            $root.'/Media/Infrastructure/Persistence/PostgreSql/PostgreSqlMediaCollectionRepository.php',
            $root.'/AdministrationAudit/Infrastructure/Persistence/PostgreSql/PostgreSqlAdministrativeActionRepository.php',
        ];
    }
}
