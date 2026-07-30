<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class InfrastructureBaselineArchitectureTest extends TestCase
{
    public function test_domain_does_not_depend_on_laravel_or_infrastructure(): void
    {
        foreach ($this->phpFilesIn($this->modulesPath()) as $file) {
            if (! str_contains($this->relativePath($file), DIRECTORY_SEPARATOR.'Domain'.DIRECTORY_SEPARATOR)) {
                continue;
            }

            $contents = $this->contentsOf($file);

            self::assertDoesNotMatchRegularExpression(
                '/(?:Illuminate|Laravel)\\\\/i',
                $contents,
                $this->relativePath($file).' imports Laravel from Domain.',
            );
            self::assertDoesNotMatchRegularExpression(
                '/\\\\Infrastructure\\\\/i',
                $contents,
                $this->relativePath($file).' depends on Infrastructure from Domain.',
            );
        }
    }

    #[DataProvider('forbiddenPersistenceProvider')]
    public function test_production_code_contains_no_forbidden_persistence_implementation(
        string $description,
        string $pattern,
    ): void {
        foreach ($this->productionPhpFiles() as $file) {
            if (($description === 'SQL' || $description === 'database access') && $this->isAuthorizedPostgreSqlInfrastructure($file)) {
                continue;
            }

            self::assertDoesNotMatchRegularExpression(
                $pattern,
                $this->contentsOf($file),
                $this->relativePath($file).' contains forbidden '.$description.'.',
            );
        }
    }

    public function test_only_explicitly_authorized_concrete_repositories_exist(): void
    {
        $repositories = [];
        foreach ($this->productionPhpFiles() as $file) {
            $tokens = token_get_all($this->contentsOf($file));

            for ($index = 0, $count = count($tokens); $index < $count; $index++) {
                if (! is_array($tokens[$index]) || $tokens[$index][0] !== T_CLASS) {
                    continue;
                }
                $previous = $index - 1;
                while ($previous >= 0 && is_array($tokens[$previous]) && $tokens[$previous][0] === T_WHITESPACE) {
                    $previous--;
                }
                if ($previous >= 0 && is_array($tokens[$previous]) && $tokens[$previous][0] === T_DOUBLE_COLON) {
                    continue;
                }

                $name = $this->nextNamedToken($tokens, $index + 1);

                if ($name !== null && str_ends_with($name, 'Repository')) {
                    $repositories[$name] = str_replace(DIRECTORY_SEPARATOR, '/', $this->relativePath($file));
                }
            }
        }
        self::assertSame([
            'PostgreSqlAdministrativeActionContextualTransitionRepository' => 'src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/PostgreSqlAdministrativeActionContextualTransitionRepository.php',
            'PostgreSqlAdministrativeActionLifecycleRepository' => 'src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/PostgreSqlAdministrativeActionLifecycleRepository.php',
            'PostgreSqlAdministrativeActionRepository' => 'src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/PostgreSqlAdministrativeActionRepository.php',
            'PostgreSqlLeadLifecycleContextualTransitionRepository' => 'src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/PostgreSqlLeadLifecycleContextualTransitionRepository.php',
            'PostgreSqlLeadLifecycleWorkflowRepository' => 'src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/PostgreSqlLeadLifecycleWorkflowRepository.php',
            'PostgreSqlAccountRepository' => 'src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/PostgreSqlAccountRepository.php',
            'PostgreSqlListingPublicationWorkflowRepository' => 'src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/PostgreSqlListingPublicationWorkflowRepository.php',
            'PostgreSqlListingRepository' => 'src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/PostgreSqlListingRepository.php',
            'PostgreSqlMediaCollectionRepository' => 'src/Modules/Media/Infrastructure/Persistence/PostgreSql/PostgreSqlMediaCollectionRepository.php',
            'PostgreSqlMediaItemLifecycleContextualTransitionRepository' => 'src/Modules/Media/Infrastructure/Persistence/PostgreSql/PostgreSqlMediaItemLifecycleContextualTransitionRepository.php',
            'PostgreSqlMediaItemLifecycleWorkflowRepository' => 'src/Modules/Media/Infrastructure/Persistence/PostgreSql/PostgreSqlMediaItemLifecycleWorkflowRepository.php',
            'PostgreSqlProfessionalStatusContextualTransitionRepository' => 'src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/PostgreSqlProfessionalStatusContextualTransitionRepository.php',
            'PostgreSqlProfessionalStatusWorkflowRepository' => 'src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/PostgreSqlProfessionalStatusWorkflowRepository.php',
            'PostgreSqlPropertyLifecycleWorkflowRepository' => 'src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/PostgreSqlPropertyLifecycleWorkflowRepository.php',
            'PostgreSqlPropertyRepository' => 'src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/PostgreSqlPropertyRepository.php',
            'PostgreSqlReservationLifecycleWorkflowRepository' => 'src/Modules/ReservationLifecycle/Infrastructure/Persistence/PostgreSql/PostgreSqlReservationLifecycleWorkflowRepository.php',
            'PostgreSqlAdministrativeActionLifecycleInboxRepository' => 'app/Infrastructure/AdministrativeActionLifecycleEventRouting/PostgreSql/PostgreSqlAdministrativeActionLifecycleInboxRepository.php',
            'PostgreSqlLeadLifecycleInboxRepository' => 'app/Infrastructure/LeadLifecycleEventRouting/PostgreSql/PostgreSqlLeadLifecycleInboxRepository.php',
            'PostgreSqlMediaItemLifecycleInboxRepository' => 'app/Infrastructure/MediaItemLifecycleEventRouting/PostgreSql/PostgreSqlMediaItemLifecycleInboxRepository.php',
            'PostgreSqlPlaceLifecycleInboxRepository' => 'app/Infrastructure/PlaceLifecycleEventRouting/PostgreSql/PostgreSqlPlaceLifecycleInboxRepository.php',
            'PostgreSqlProfessionalStatusInboxRepository' => 'app/Infrastructure/ProfessionalStatusEventRouting/PostgreSql/PostgreSqlProfessionalStatusInboxRepository.php',
            'PostgreSqlReservationLifecycleInboxRepository' => 'app/Infrastructure/ReservationLifecycleEventRouting/PostgreSql/PostgreSqlReservationLifecycleInboxRepository.php',
        ], $repositories);
    }

    public function test_modules_do_not_import_other_modules(): void
    {
        foreach ($this->phpFilesIn($this->modulesPath()) as $file) {
            $relative = $this->relativePath($file);
            $parts = explode(DIRECTORY_SEPARATOR, $relative);
            $owner = $parts[2] ?? null;

            self::assertNotNull($owner, 'Unable to identify module owner for '.$relative.'.');

            preg_match_all('/Appart\\\\Modules\\\\([A-Za-z][A-Za-z0-9]*)\\\\/', $this->contentsOf($file), $matches);

            foreach (array_unique($matches[1]) as $dependency) {
                self::assertSame($owner, $dependency, $relative.' directly depends on module '.$dependency.'.');
            }
        }
    }

    public function test_application_does_not_depend_on_infrastructure(): void
    {
        foreach ($this->phpFilesIn($this->modulesPath()) as $file) {
            if (! str_contains($this->relativePath($file), DIRECTORY_SEPARATOR.'Application'.DIRECTORY_SEPARATOR)) {
                continue;
            }

            self::assertDoesNotMatchRegularExpression(
                '/\\\\Infrastructure\\\\/i',
                $this->contentsOf($file),
                $this->relativePath($file).' reverses the Application/Infrastructure dependency.',
            );
        }
    }

    public function test_infrastructure_does_not_depend_on_application_use_cases(): void
    {
        foreach ($this->phpFilesIn($this->modulesPath()) as $file) {
            if (! str_contains($this->relativePath($file), DIRECTORY_SEPARATOR.'Infrastructure'.DIRECTORY_SEPARATOR)) {
                continue;
            }

            self::assertDoesNotMatchRegularExpression(
                '/\\\\Application\\\\UseCase\\\\/i',
                $this->contentsOf($file),
                $this->relativePath($file).' makes Infrastructure depend on an Application use case.',
            );
        }

        self::addToAssertionCount(1);
    }

    public function test_shared_registry_contracts_are_framework_and_backend_agnostic(): void
    {
        $contractsPath = $this->projectPath('tests'.DIRECTORY_SEPARATOR.'Unit'.DIRECTORY_SEPARATOR.'Contracts');

        foreach ($this->phpFilesIn($contractsPath) as $file) {
            self::assertDoesNotMatchRegularExpression(
                '/(?:Illuminate\\\\|Laravel\\\\|Eloquent|PostgreSQL|\\bPDO\\b|\\bSQL\\b|\\\\Adapters?\\\\)/i',
                $this->contentsOf($file),
                $this->relativePath($file).' couples a shared contract to a framework or backend.',
            );
        }
    }

    public function test_contract_backend_entries_do_not_redefine_shared_scenarios(): void
    {
        $contractPaths = [
            $this->projectPath('tests'.DIRECTORY_SEPARATOR.'Unit'.DIRECTORY_SEPARATOR.'Contracts'),
            $this->projectPath('tests'.DIRECTORY_SEPARATOR.'PostgreSQL'),
        ];

        foreach ($contractPaths as $contractsPath) {
            foreach ($this->phpFilesIn($contractsPath) as $file) {
                if (! str_ends_with($file->getFilename(), 'ContractTest.php')) {
                    continue;
                }

                self::assertDoesNotMatchRegularExpression(
                    '/function\\s+test_/i',
                    $this->contentsOf($file),
                    $this->relativePath($file).' duplicates a scenario from its abstract contract.',
                );
            }
        }

        self::addToAssertionCount(1);
    }

    public function test_listing_contracts_use_only_public_domain_and_registry_apis(): void
    {
        $path = $this->projectPath('tests'.DIRECTORY_SEPARATOR.'Unit'.DIRECTORY_SEPARATOR.'Contracts'.DIRECTORY_SEPARATOR.'ListingLifecycle');

        foreach ($this->phpFilesIn($path) as $file) {
            self::assertDoesNotMatchRegularExpression(
                '/(?:Reflection(?:Class|Property|Method)|setAccessible|Closure::bind|private\s+array\s+\$listings)/i',
                $this->contentsOf($file),
                $this->relativePath($file).' introspects Fake internals.',
            );
        }
    }

    public function test_listing_persistence_is_limited_to_its_authorized_slice(): void
    {
        foreach ($this->productionPhpFiles() as $file) {
            $relative = str_replace(DIRECTORY_SEPARATOR, '/', $this->relativePath($file));
            if (! str_contains($relative, '/ListingLifecycle/')) {
                continue;
            }

            if (str_contains($relative, '/Infrastructure/')) {
                self::assertStringContainsString('/ListingLifecycle/Infrastructure/Persistence/', $relative);
            }
        }
    }

    public function test_property_persistence_is_limited_to_its_authorized_slice(): void
    {
        foreach ($this->productionPhpFiles() as $file) {
            $relative = str_replace(DIRECTORY_SEPARATOR, '/', $this->relativePath($file));
            if (! str_contains($relative, '/RealEstateCatalog/')) {
                continue;
            }

            if (str_contains($relative, '/Infrastructure/')) {
                self::assertStringContainsString('/RealEstateCatalog/Infrastructure/Persistence/', $relative);
            }
        }
    }

    public function test_media_persistence_is_confined_to_its_dedicated_slice(): void
    {
        foreach ($this->productionPhpFiles() as $file) {
            $relative = str_replace(DIRECTORY_SEPARATOR, '/', $this->relativePath($file));
            if (! str_contains($relative, '/Media/')) {
                continue;
            }

            if (str_contains($relative, '/Infrastructure/')) {
                self::assertStringContainsString('/Media/Infrastructure/Persistence/', $relative);
            }
        }
    }

    public function test_persistence_types_are_aggregate_specific_and_never_shared(): void
    {
        foreach ($this->productionPhpFiles() as $file) {
            $relative = str_replace(DIRECTORY_SEPARATOR, '/', $this->relativePath($file));
            $contents = $this->contentsOf($file);

            self::assertDoesNotMatchRegularExpression(
                '/(?:class|interface|trait)\s+(?:(?:Abstract|Base|Generic|Shared))?(?:Repository|Mapper|Snapshot)\b/i',
                $contents,
                $relative.' declares a generic persistence abstraction.',
            );

            if (preg_match('/(?:class|interface|trait)\s+\w+(?:Repository|Mapper|Snapshot)\b/i', $contents) === 1) {
                if (in_array($relative, ['app/Http/AdministrativeActionLifecycleHttpResultMapper.php', 'app/Http/LeadLifecycleHttpResultMapper.php', 'app/Http/MediaItemLifecycleHttpResultMapper.php', 'app/Http/ModerationHttpResponseMapper.php', 'app/Http/PlaceLifecycleHttpResultMapper.php', 'app/Http/ProfessionalStatusHttpResultMapper.php', 'app/Http/ReservationLifecycleHttpResultMapper.php'], true)) {
                    self::assertStringContainsString('namespace App\\Http;', $contents);
                    self::assertStringNotContainsString('PDO', $contents);
                    if ($relative === 'app/Http/ModerationHttpResponseMapper.php') {
                        foreach (['SELECT ', 'INSERT ', 'UPDATE ', 'DELETE ', 'Repository', 'Snapshot', 'Persistence'] as $forbidden) {
                            self::assertStringNotContainsString($forbidden, $contents);
                        }
                    }

                    continue;
                }
                if (str_starts_with($relative, 'app/Infrastructure/PublicProjectionOutbox/PostgreSql/')) {
                    self::assertStringContainsString('PublicProjectionOutbox', $contents);

                    continue;
                }
                if (str_starts_with($relative, 'app/Infrastructure/PublicProjectionStore/PostgreSql/')) {
                    self::assertStringContainsString('PublicListingProjection', $contents);

                    continue;
                }
                if (str_starts_with($relative, 'app/Infrastructure/PublicGeographySource/PostgreSql/')) {
                    self::assertStringContainsString('PublicGeography', $contents);

                    continue;
                }
                if (str_starts_with($relative, 'app/Infrastructure/PublicMediaSource/PostgreSql/')) {
                    self::assertStringContainsString('PublicMedia', $contents);

                    continue;
                }
                if (str_starts_with($relative, 'app/Infrastructure/ActiveGenerationReader/PostgreSql/')) {
                    self::assertStringContainsString('ActiveGeneration', $contents);

                    continue;
                }
                if (str_starts_with($relative, 'app/Infrastructure/DecisionTimeSource/')) {
                    self::assertStringContainsString('DecisionTime', $contents);

                    continue;
                }
                if (str_starts_with($relative, 'app/Infrastructure/ReservationLifecycleEventRouting/PostgreSql/')) {
                    self::assertStringContainsString('ReservationLifecycleInbox', $contents);

                    continue;
                }
                if (str_starts_with($relative, 'app/Infrastructure/LeadLifecycleEventRouting/PostgreSql/')) {
                    self::assertStringContainsString('LeadLifecycleInbox', $contents);

                    continue;
                }
                if (str_starts_with($relative, 'app/Infrastructure/ProfessionalStatusEventRouting/PostgreSql/')) {
                    self::assertStringContainsString('ProfessionalStatusInbox', $contents);

                    continue;
                }
                if (str_starts_with($relative, 'app/Infrastructure/MediaItemLifecycleEventRouting/PostgreSql/')) {
                    self::assertStringContainsString('MediaItemLifecycleInbox', $contents);

                    continue;
                }
                if (str_starts_with($relative, 'app/Infrastructure/AdministrativeActionLifecycleEventRouting/PostgreSql/')) {
                    self::assertStringContainsString('AdministrativeActionLifecycleInbox', $contents);

                    continue;
                }
                if (str_starts_with($relative, 'app/Infrastructure/PlaceLifecycleEventRouting/PostgreSql/')) {
                    self::assertStringContainsString('PlaceLifecycleInbox', $contents);

                    continue;
                }
                self::assertStringContainsString('/Modules/', '/'.$relative, $relative.' declares shared persistence outside a module.');
                self::assertStringContainsString('/Infrastructure/Persistence/', '/'.$relative, $relative.' places persistence outside module Infrastructure.');
            }
        }
    }

    public function test_domain_and_application_never_declare_persistence_implementations(): void
    {
        foreach ($this->phpFilesIn($this->modulesPath()) as $file) {
            $relative = $this->relativePath($file);
            $inDomain = str_contains($relative, DIRECTORY_SEPARATOR.'Domain'.DIRECTORY_SEPARATOR);
            $inApplication = str_contains($relative, DIRECTORY_SEPARATOR.'Application'.DIRECTORY_SEPARATOR);

            if (! $inDomain && ! $inApplication) {
                continue;
            }

            $forbidden = $inDomain ? '(?:Repository|Mapper|Snapshot)' : 'Repository';
            self::assertDoesNotMatchRegularExpression(
                '/(?:class|interface|trait)\s+\w*'.$forbidden.'\b/i',
                $this->contentsOf($file),
                $relative.' declares a forbidden persistence type outside Infrastructure.',
            );
        }
    }

    public function test_sql_and_pdo_are_confined_to_module_infrastructure(): void
    {
        foreach ($this->productionPhpFiles() as $file) {
            $contents = $this->contentsOf($file);
            if (preg_match('/(?:\bPDO\b|\bSELECT\b|\bINSERT\s+INTO\b|\bUPDATE\b.+\bSET\b|\bDELETE\s+FROM\b|\bCREATE\s+(?:TABLE|SCHEMA)\b)/is', $contents) !== 1) {
                continue;
            }

            $relative = str_replace(DIRECTORY_SEPARATOR, '/', $this->relativePath($file));
            self::assertTrue(
                preg_match('#^src/Modules/[^/]+/Infrastructure/#', $relative) === 1
                    || str_starts_with($relative, 'app/Infrastructure/PublicProjectionOutbox/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/IdentityAccessEventOutbox/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/MediaIngestionEventOutbox/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/ModerationEventDelivery/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/ModerationAtomicOperation/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/ModerationEventOutbox/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/PublicProjectionStore/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/PublicGeographySource/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/PublicMediaSource/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/ActiveGenerationReader/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/ProjectionRebuildRuntimeSource/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/PropertyListingResolution/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/ListingPublicationEventRouting/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/PropertyLifecycleEventRouting/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/ReservationLifecycleEventRouting/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/LeadLifecycleEventRouting/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/ProfessionalStatusEventRouting/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/MediaItemLifecycleEventRouting/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/AdministrativeActionLifecycleEventRouting/PostgreSql/')
                    || str_starts_with($relative, 'app/Infrastructure/PlaceLifecycleEventRouting/PostgreSql/')
                    || $relative === 'app/Providers/PublicProjectionRuntimeServiceProvider.php',
                $relative.' uses SQL or PDO outside authorized Infrastructure.',
            );
        }
    }

    public function test_product_remains_an_immutable_catalog_without_registry_repository_or_events(): void
    {
        foreach ($this->productionPhpFiles() as $file) {
            self::assertDoesNotMatchRegularExpression(
                '/(?:interface|class)\\s+Product(?:Registry|Repository)\\b/',
                $this->contentsOf($file),
                $this->relativePath($file).' violates ADR-1006.',
            );

            $relative = str_replace(DIRECTORY_SEPARATOR, '/', $this->relativePath($file));
            self::assertFalse(
                str_contains($relative, '/MonetizationPayments/Domain/Event/Product'),
                $relative.' introduces a forbidden Product event.',
            );
        }
    }

    public function test_sql_migrations_are_limited_to_the_authorized_postgresql_slices(): void
    {
        $allowed = [
            'src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/Migrations/',
            'src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/',
            'src/Modules/Geography/Infrastructure/Persistence/PostgreSql/Migrations/',
            'src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/',
            'src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/',
            'src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/',
            'src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/Migrations/',
            'src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/',
            'src/Modules/ReservationLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/',
            'src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/',
            'src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/Migrations/',
            'src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/',
            'app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/',
            'app/Infrastructure/PublicProjectionStore/PostgreSql/Migrations/',
            'app/Infrastructure/PublicGeographySource/PostgreSql/Migrations/',
            'app/Infrastructure/PublicMediaSource/PostgreSql/Migrations/',
            'app/Infrastructure/ListingPublicationEventRouting/PostgreSql/Migrations/',
            'app/Infrastructure/PropertyLifecycleEventRouting/PostgreSql/Migrations/',
            'app/Infrastructure/ReservationLifecycleEventRouting/PostgreSql/Migrations/',
            'app/Infrastructure/LeadLifecycleEventRouting/PostgreSql/Migrations/',
            'app/Infrastructure/ProfessionalStatusEventRouting/PostgreSql/Migrations/',
            'app/Infrastructure/MediaItemLifecycleEventRouting/PostgreSql/Migrations/',
            'app/Infrastructure/AdministrativeActionLifecycleEventRouting/PostgreSql/Migrations/',
            'app/Infrastructure/PlaceLifecycleEventRouting/PostgreSql/Migrations/',
        ];

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->projectPath())) as $file) {
            if (! $file->isFile() || strtolower($file->getExtension()) !== 'sql') {
                continue;
            }

            $relative = str_replace(DIRECTORY_SEPARATOR, '/', $this->relativePath($file));
            self::assertTrue(array_any($allowed, static fn (string $prefix): bool => str_starts_with($relative, $prefix)), $relative.' is outside the authorized PostgreSQL slices.');
        }

        self::addToAssertionCount(1);
    }

    public static function forbiddenPersistenceProvider(): array
    {
        return [
            'Eloquent' => ['Eloquent', '/(?:Illuminate\\\\Database\\\\Eloquent|extends\\s+(?:\\\\?Illuminate\\\\Database\\\\Eloquent\\\\)?Model\\b)/i'],
            'database access' => ['database access', '/(?:\\bDB::|\\bPDO\\b|Doctrine\\\\DBAL|pg_(?:connect|query|prepare)|mysqli?_)/i'],
            'SQL' => ['SQL', '/(?:\bSELECT\b.+\bFROM\b|\bINSERT\s+INTO\b|\bUPDATE\b.+\bSET\b|\bDELETE\s+FROM\b|\bCREATE\s+(?:TABLE|SCHEMA)\b|\bALTER\s+TABLE\b)/is'],
        ];
    }

    /** @return list<SplFileInfo> */
    private function productionPhpFiles(): array
    {
        return [
            ...$this->phpFilesIn($this->projectPath('src')),
            ...$this->phpFilesIn($this->projectPath('app')),
        ];
    }

    /** @return list<SplFileInfo> */
    private function phpFilesIn(string $path): array
    {
        if (! is_dir($path)) {
            return [];
        }

        $files = [];

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file;
            }
        }

        usort($files, static fn (SplFileInfo $left, SplFileInfo $right): int => strcmp($left->getPathname(), $right->getPathname()));

        return $files;
    }

    private function nextNamedToken(array $tokens, int $index): ?string
    {
        for ($count = count($tokens); $index < $count; $index++) {
            if (is_array($tokens[$index]) && $tokens[$index][0] === T_STRING) {
                return $tokens[$index][1];
            }

            if ($tokens[$index] === '{' || $tokens[$index] === '(') {
                return null;
            }
        }

        return null;
    }

    private function contentsOf(SplFileInfo $file): string
    {
        $contents = file_get_contents($file->getPathname());

        self::assertIsString($contents, 'Unable to read '.$this->relativePath($file).'.');

        return $contents;
    }

    private function relativePath(SplFileInfo $file): string
    {
        return substr($file->getPathname(), strlen($this->projectPath()) + 1);
    }

    private function isAuthorizedPostgreSqlInfrastructure(SplFileInfo $file): bool
    {
        $relative = str_replace(DIRECTORY_SEPARATOR, '/', $this->relativePath($file));

        return str_starts_with($relative, 'src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/')
            || str_starts_with($relative, 'src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/')
            || str_starts_with($relative, 'src/Modules/Geography/Infrastructure/Persistence/PostgreSql/')
            || str_starts_with($relative, 'src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/')
            || str_starts_with($relative, 'src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/')
            || str_starts_with($relative, 'src/Modules/Media/Infrastructure/Persistence/PostgreSql/')
            || str_starts_with($relative, 'src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/')
            || str_starts_with($relative, 'src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/')
            || str_starts_with($relative, 'src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/')
            || str_starts_with($relative, 'src/Modules/ReservationLifecycle/Infrastructure/Persistence/PostgreSql/')
            || str_starts_with($relative, 'src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/')
            || str_starts_with($relative, 'src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/PublicProjectionOutbox/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/IdentityAccessEventOutbox/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/MediaIngestionEventOutbox/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/ModerationEventDelivery/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/ModerationAtomicOperation/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/ModerationEventOutbox/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/PublicProjectionStore/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/PublicGeographySource/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/PublicMediaSource/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/ActiveGenerationReader/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/ProjectionRebuildRuntimeSource/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/PropertyListingResolution/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/ListingPublicationEventRouting/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/PropertyLifecycleEventRouting/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/ReservationLifecycleEventRouting/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/LeadLifecycleEventRouting/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/ProfessionalStatusEventRouting/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/MediaItemLifecycleEventRouting/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/AdministrativeActionLifecycleEventRouting/PostgreSql/')
            || str_starts_with($relative, 'app/Infrastructure/PlaceLifecycleEventRouting/PostgreSql/')
            || $relative === 'app/Providers/PublicProjectionRuntimeServiceProvider.php';
    }

    private function modulesPath(): string
    {
        return $this->projectPath('src'.DIRECTORY_SEPARATOR.'Modules');
    }

    private function projectPath(string $suffix = ''): string
    {
        $root = dirname(__DIR__, 2);

        return $suffix === '' ? $root : $root.DIRECTORY_SEPARATOR.$suffix;
    }
}
