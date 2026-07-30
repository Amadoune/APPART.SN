<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AccountStatusConsumerCompatibilityAmendmentArchitectureTest extends TestCase
{
    public function test_missing_routed_destination_boundary_is_documented_without_implementation(): void
    {
        $root = dirname(__DIR__, 2);
        $amendment = file_get_contents(
            $root.'/docs/ACCOUNT-STATUS-CONSUMER-COMPATIBILITY-AMENDMENT.md',
        );
        $matrix = file_get_contents(
            $root.'/docs/ACCOUNT-STATUS-CONSUMER-COMPATIBILITY-MATRIX.md',
        );
        $certification = file_get_contents(
            $root.'/docs/PHASE-4.9J-R2-CONSUMER-COMPATIBILITY-CERTIFICATION.md',
        );

        self::assertIsString($amendment);
        self::assertIsString($matrix);
        self::assertIsString($certification);
        self::assertStringContainsString('J5 Consumer Compatibility', $amendment);
        self::assertStringContainsString('NON SATISFAIT', $amendment);
        self::assertStringContainsString('Destination routée absente', $matrix);
        self::assertStringContainsString('NO GO CERTIFIÉ ET FERMÉ', $certification);
        self::assertStringContainsString('4.9J', $certification);
        self::assertStringContainsString('RESTE FERMÉ', $certification);

        foreach ([
            $root.'/database/migrations',
            $root.'/app/Application/AccountStatusEventConsumption',
            $root.'/app/Application/PublicProjectionOutbox',
        ] as $path) {
            self::assertDirectoryExists($path);
        }
    }
}
