<?php

namespace Tests\Architecture;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\AccessibilityComplianceStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\EndToEndReadinessStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\PerformanceReadinessStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ReleaseCandidateStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ResponsiveComplianceStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\UserAcceptanceStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\UserExperienceStatusV1;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ExperienceAcceptanceContractsArchitectureTest extends TestCase
{
    public function test_contract_enclave_is_minimal_and_read_only(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ExperienceAcceptance/Application/PublicRead';
        $files = [];
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
                $php .= (string) file_get_contents($file->getPathname());
            }
        }
        self::assertCount(22, $files);
        foreach (['ResponsiveCompliance', 'AccessibilityCompliance', 'UserExperience', 'EndToEndReadiness', 'PerformanceReadiness', 'UserAcceptance', 'ReleaseCandidate'] as $family) {
            self::assertStringContainsString('interface '.$family.'ReaderV1', $php);
            self::assertStringContainsString('final readonly class '.$family.'ResultV1', $php);
            self::assertStringContainsString('enum '.$family.'StatusV1', $php);
        }
        self::assertSame(7, substr_count($php, 'public function read('));
        foreach (['write(', 'append(', 'score', 'percentage', 'metric', 'report', 'Infrastructure\\', 'Persistence', 'Runtime', 'Http', 'Event', 'Delivery', 'Outbox', 'Provider', 'PDO', 'PostgreSql', 'SQL', 'Transport', 'Routing', 'Consumer'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_seven_status_catalogues_are_exactly_closed(): void
    {
        $expected = ['available', 'missing', 'corrupted', 'dependency_unavailable'];
        self::assertSame($expected, array_column(ResponsiveComplianceStatusV1::cases(), 'value'));
        self::assertSame($expected, array_column(AccessibilityComplianceStatusV1::cases(), 'value'));
        self::assertSame($expected, array_column(UserExperienceStatusV1::cases(), 'value'));
        self::assertSame($expected, array_column(EndToEndReadinessStatusV1::cases(), 'value'));
        self::assertSame($expected, array_column(PerformanceReadinessStatusV1::cases(), 'value'));
        self::assertSame($expected, array_column(UserAcceptanceStatusV1::cases(), 'value'));
        self::assertSame($expected, array_column(ReleaseCandidateStatusV1::cases(), 'value'));
    }
}
