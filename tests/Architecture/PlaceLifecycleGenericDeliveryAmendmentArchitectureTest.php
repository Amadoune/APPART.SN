<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PlaceLifecycleGenericDeliveryAmendmentArchitectureTest extends TestCase
{
    public function test_certified_amendment_selects_direct_compatibility_without_adapter(): void
    {
        $root = dirname(__DIR__, 2);
        $amendment = (string) file_get_contents(
            $root.'/docs/PHASE-4.8J-R2-GENERIC-DELIVERY-CONTRACT-COMPATIBILITY-AMENDMENT.md',
        );

        self::assertStringContainsString('**Option 1 retenue**', $amendment);
        self::assertStringContainsString('Option 2 — NO GO', $amendment);
        self::assertStringContainsString('Option 3 — NO GO', $amendment);
        self::assertStringContainsString('aucun nouveau Payload, Consumer ou adapter', $amendment);
        self::assertFileExists(
            $root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/040_geography_outbox_owner.sql',
        );
    }
}
