<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PlaceLifecycleGenericDeliveryOutputAmendmentArchitectureTest extends TestCase
{
    public function test_r3_defines_the_unique_checksum_projection_without_a_second_calculation(): void
    {
        $amendment = $this->document('docs/PHASE-4.8J-R3-GENERIC-DELIVERY-OUTPUT-TYPE-AMENDMENT.md');

        self::assertStringContainsString('transportChecksum()', $amendment);
        self::assertStringContainsString('checksum()', $amendment);
        self::assertStringContainsString('retourne exclusivement transportChecksum()->value', $amendment);
        self::assertStringContainsString("n'ajoute aucun Payload, Consumer, Writer, Reader, Mapper, Worker ou\nadapter", $amendment);
    }

    public function test_r3_defines_a_total_bijection_for_place_consumption_outputs(): void
    {
        $matrix = $this->document('docs/PLACE-LIFECYCLE-GENERIC-DELIVERY-OUTPUT-MATRIX.md');

        self::assertStringContainsString('Acknowledged ↔ Consumed', $matrix);
        self::assertStringContainsString('Retry        ↔ RetryableFailure', $matrix);
        self::assertStringContainsString('Quarantined ↔ PermanentFailure', $matrix);
        self::assertStringContainsString('Les sept autres résultats génériques ne sont pas des sorties possibles', $matrix);
    }

    public function test_r3_and_4_8_j_remain_certified_after_later_foundations(): void
    {
        $certification = $this->document('docs/PHASE-4.8J-R3-CERTIFICATION.md');
        $roadmap = $this->document('docs/PHASE-4.8-ROADMAP.md');

        self::assertStringContainsString('4.8J-R3 → GO CERTIFIÉ et fermé', $certification);
        self::assertStringContainsString('4.8J → AUTORISÉ', $certification);
        self::assertStringContainsString('4.8J — Outbox Compatibility', $roadmap);
        self::assertStringContainsString('Consumer et Worker génériques compatibles — **GO CERTIFIÉ et fermé**', $roadmap);
    }

    private function document(string $relativePath): string
    {
        $content = file_get_contents(dirname(__DIR__, 2).'/'.$relativePath);
        self::assertIsString($content);

        return $content;
    }
}
