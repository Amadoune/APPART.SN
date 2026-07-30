<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AccountStatusRoutedDeliveryBoundaryAmendmentArchitectureTest extends TestCase
{
    public function test_routed_delivery_boundary_is_complete_and_documentary(): void
    {
        $root = dirname(__DIR__, 2);
        $boundary = file_get_contents(
            $root.'/docs/PUBLIC-PROJECTION-ROUTED-DELIVERY-BOUNDARY-V1.md',
        );
        $responsibilities = file_get_contents(
            $root.'/docs/ACCOUNT-STATUS-ROUTED-DELIVERY-RESPONSIBILITY-MATRIX.md',
        );
        $compatibility = file_get_contents(
            $root.'/docs/PUBLIC-PROJECTION-ROUTED-DELIVERY-COMPATIBILITY-MATRIX.md',
        );
        $certification = file_get_contents(
            $root.'/docs/PHASE-4.9J-R3-ROUTED-DELIVERY-BOUNDARY-CERTIFICATION.md',
        );

        self::assertIsString($boundary);
        self::assertIsString($responsibilities);
        self::assertIsString($compatibility);
        self::assertIsString($certification);
        self::assertStringContainsString('PublicProjectionRoutedDeliveryMessageV1', $boundary);
        self::assertStringContainsString('consumeRouted', $boundary);
        self::assertStringContainsString('Router 4.9H', $responsibilities);
        self::assertStringContainsString('Dix owners historiques', $compatibility);
        self::assertStringContainsString('GO CERTIFIÉ ET FERMÉ', $certification);
        self::assertStringContainsString('OUVERT POUR IMPLÉMENTATION', $certification);

        self::assertFileDoesNotExist(
            $root.'/database/migrations/043_create_identity_access_public_projection_outbox.php',
        );
    }
}
