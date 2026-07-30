<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PlaceLifecycleOutboxOwnerResolutionArchitectureTest extends TestCase
{
    public function test_r1_reservation_is_applied_by_the_authorized_4_8_j_sprint(): void
    {
        $root = dirname(__DIR__, 2);
        $specification = (string) file_get_contents(
            $root.'/docs/PLACE-LIFECYCLE-OUTBOX-OWNER-SPECIFICATION.md',
        );

        self::assertStringContainsString('OutboxOwner              Geography', $specification);
        self::assertStringContainsString('SchemaOwner              geography', $specification);
        self::assertStringContainsString('migration future réservée est **040**', $specification);
        self::assertStringContainsString('Aucun Writer, Reader, Mapper, Consumer ou Worker spécialisé', $specification);
        self::assertFileExists(
            $root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/040_geography_outbox_owner.sql',
        );
        self::assertStringContainsString(
            "'Geography' => 'geography'",
            (string) file_get_contents(
                $root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxSchema.php',
            ),
        );
    }
}
