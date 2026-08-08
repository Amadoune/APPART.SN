<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class LeadIngressAntiAbusePublicReadArchitectureTest extends TestCase
{
    public function test_contracts_are_owner_scoped_framework_agnostic_and_contract_only(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/LeadIngressAntiAbusePublicRead';
        $files = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $files[] = $file->getPathname();
            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            self::assertStringContainsString('namespace Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead', $contents);
            foreach (['Illuminate\\', 'Infrastructure\\', 'PDO', 'PostgreSql', 'Runtime\\', 'Persistence\\', 'App\\Providers', 'App\\Http', 'Moderation', 'AdministrationAudit', 'IdentityAccess', 'ListingLifecycle', 'Professional'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }

        self::assertCount(4, $files);
    }

    public function test_public_contracts_do_not_depend_on_implementation_or_provider(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/LeadIngressAntiAbusePublicRead';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            self::assertStringNotContainsString('AntiAbuseOwnerReader', $contents, $file->getPathname());
            self::assertStringNotContainsString('App\\Providers', $contents, $file->getPathname());
        }
    }
}
