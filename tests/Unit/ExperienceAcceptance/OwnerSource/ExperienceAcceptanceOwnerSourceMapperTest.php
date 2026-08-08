<?php

namespace Tests\Unit\ExperienceAcceptance\OwnerSource;

use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceRevisionState;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceStream;
use Appart\Modules\ExperienceAcceptance\Infrastructure\Persistence\ExperienceAcceptanceOwnerSourceMapper;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExperienceAcceptanceOwnerSourceMapperTest extends TestCase
{
    #[DataProvider('streams')]
    public function test_round_trip_is_canonical(ExperienceAcceptanceStream $stream, string $decision): void
    {
        $mapper = new ExperienceAcceptanceOwnerSourceMapper;
        $state = self::state($stream, $decision);
        $row = $mapper->toRow($state);
        $restored = $mapper->toState($row);
        self::assertSame($state->scopeKey, $restored->scopeKey);
        self::assertSame($state->stream, $restored->stream);
        self::assertSame($state->revision, $restored->revision);
        self::assertSame($state->decision, $restored->decision);
        self::assertSame('2026-08-08T10:00:00.123456Z', $row['effective_at']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $row['revision_checksum']);
    }

    public function test_checksum_corruption_is_rejected(): void
    {
        $mapper = new ExperienceAcceptanceOwnerSourceMapper;
        $row = $mapper->toRow(self::state(ExperienceAcceptanceStream::ResponsiveCompliance, 'available'));
        $row['revision_checksum'] = str_repeat('0', 64);
        $this->expectException(RuntimeException::class);
        $mapper->toState($row);
    }

    /** @return iterable<string, array{ExperienceAcceptanceStream,string}> */
    public static function streams(): iterable
    {
        yield 'responsive_compliance' => [ExperienceAcceptanceStream::ResponsiveCompliance, 'available'];
        yield 'accessibility_compliance' => [ExperienceAcceptanceStream::AccessibilityCompliance, 'available'];
        yield 'user_experience' => [ExperienceAcceptanceStream::UserExperience, 'available'];
        yield 'end_to_end_readiness' => [ExperienceAcceptanceStream::EndToEndReadiness, 'available'];
        yield 'performance_readiness' => [ExperienceAcceptanceStream::PerformanceReadiness, 'available'];
        yield 'user_acceptance' => [ExperienceAcceptanceStream::UserAcceptance, 'available'];
        yield 'release_candidate' => [ExperienceAcceptanceStream::ReleaseCandidate, 'available'];
    }

    private static function state(ExperienceAcceptanceStream $stream, string $decision): ExperienceAcceptanceRevisionState
    {
        return new ExperienceAcceptanceRevisionState('experience:primary', $stream, 1, $decision, new DateTimeImmutable('2026-08-08T12:00:00.123456+02:00'), new DateTimeImmutable('2026-08-08T12:00:01.123456+02:00'));
    }
}
