<?php

namespace Tests\Unit\ExperienceAcceptance\OwnerReader;

use Appart\Modules\ExperienceAcceptance\Application\OwnerReader\AccessibilityComplianceOwnerReader;
use Appart\Modules\ExperienceAcceptance\Application\OwnerReader\EndToEndReadinessOwnerReader;
use Appart\Modules\ExperienceAcceptance\Application\OwnerReader\PerformanceReadinessOwnerReader;
use Appart\Modules\ExperienceAcceptance\Application\OwnerReader\ReleaseCandidateOwnerReader;
use Appart\Modules\ExperienceAcceptance\Application\OwnerReader\ResponsiveComplianceOwnerReader;
use Appart\Modules\ExperienceAcceptance\Application\OwnerReader\UserAcceptanceOwnerReader;
use Appart\Modules\ExperienceAcceptance\Application\OwnerReader\UserExperienceOwnerReader;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceOwnerSource;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceReadResult;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceRevisionState;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceStream;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\AccessibilityComplianceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\EndToEndReadinessReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\PerformanceReadinessReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\ReleaseCandidateReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\ResponsiveComplianceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\UserAcceptanceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\UserExperienceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ExperienceAcceptanceOwnerReaderTest extends TestCase
{
    #[DataProvider('readers')]
    public function test_found_decision_is_reduced_homonymously(string $reader, ExperienceAcceptanceStream $stream, string $decision): void
    {
        $source = $this->source(ExperienceAcceptanceReadResult::found(new ExperienceAcceptanceRevisionState('experience:primary', $stream, 1, $decision, self::at(), self::at())));
        $result = $this->reader($reader, $source)->read(self::observedAt());
        self::assertSame($decision, $result->status->value);
        self::assertSame('2026-08-08T10:00:00.123456Z', $result->observedAt);
        self::assertSame(['observedAt', 'status'], array_keys(get_object_vars($result)));
    }

    #[DataProvider('readers')]
    public function test_structural_states_are_reduced_homonymously(string $reader, ExperienceAcceptanceStream $stream, string $decision): void
    {
        unset($stream, $decision);
        foreach ([
            [ExperienceAcceptanceReadResult::missing(), 'missing'],
            [ExperienceAcceptanceReadResult::corrupted(), 'corrupted'],
            [ExperienceAcceptanceReadResult::dependencyUnavailable(), 'dependency_unavailable'],
        ] as [$sourceResult, $expected]) {
            $result = $this->reader($reader, $this->source($sourceResult))->read(self::observedAt());
            self::assertSame($expected, $result->status->value);
        }
    }

    /** @return iterable<string, array{string,ExperienceAcceptanceStream,string}> */
    public static function readers(): iterable
    {
        yield 'responsive' => ['responsive', ExperienceAcceptanceStream::ResponsiveCompliance, 'available'];
        yield 'accessibility' => ['accessibility', ExperienceAcceptanceStream::AccessibilityCompliance, 'available'];
        yield 'ux' => ['ux', ExperienceAcceptanceStream::UserExperience, 'available'];
        yield 'e2e' => ['e2e', ExperienceAcceptanceStream::EndToEndReadiness, 'available'];
        yield 'performance' => ['performance', ExperienceAcceptanceStream::PerformanceReadiness, 'available'];
        yield 'uat' => ['uat', ExperienceAcceptanceStream::UserAcceptance, 'available'];
        yield 'release' => ['release', ExperienceAcceptanceStream::ReleaseCandidate, 'available'];
    }

    private function source(ExperienceAcceptanceReadResult $result): ExperienceAcceptanceOwnerSource
    {
        $source = $this->createMock(ExperienceAcceptanceOwnerSource::class);
        $source->method('read')->willReturn($result);

        return $source;
    }

    private function reader(string $reader, ExperienceAcceptanceOwnerSource $source): ResponsiveComplianceReaderV1|AccessibilityComplianceReaderV1|UserExperienceReaderV1|EndToEndReadinessReaderV1|PerformanceReadinessReaderV1|UserAcceptanceReaderV1|ReleaseCandidateReaderV1
    {
        return match ($reader) {
            'responsive' => new ResponsiveComplianceOwnerReader($source),
            'accessibility' => new AccessibilityComplianceOwnerReader($source),
            'ux' => new UserExperienceOwnerReader($source),
            'e2e' => new EndToEndReadinessOwnerReader($source),
            'performance' => new PerformanceReadinessOwnerReader($source),
            'uat' => new UserAcceptanceOwnerReader($source),
            'release' => new ReleaseCandidateOwnerReader($source),
            default => throw new InvalidArgumentException('Unknown Experience Acceptance reader.'),
        };
    }

    private static function observedAt(): ExperienceAcceptanceObservedAt
    {
        return new ExperienceAcceptanceObservedAt(self::at());
    }

    private static function at(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-08T10:00:00.123456Z');
    }
}
