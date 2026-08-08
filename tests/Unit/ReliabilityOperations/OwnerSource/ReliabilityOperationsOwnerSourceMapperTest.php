<?php

namespace Tests\Unit\ReliabilityOperations\OwnerSource;

use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsRevisionState;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsStream;
use Appart\Modules\ReliabilityOperations\Infrastructure\Persistence\ReliabilityOperationsOwnerSourceMapper;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ReliabilityOperationsOwnerSourceMapperTest extends TestCase
{
    #[DataProvider('streams')]
    public function test_round_trip_is_canonical(ReliabilityOperationsStream $stream, string $decision): void
    {
        $mapper = new ReliabilityOperationsOwnerSourceMapper;
        $state = self::state($stream, $decision);
        $row = $mapper->toRow($state);
        $restored = $mapper->toState($row);
        self::assertSame($state->scopeKey, $restored->scopeKey);
        self::assertSame($state->stream, $restored->stream);
        self::assertSame($state->revision, $restored->revision);
        self::assertSame($state->decision, $restored->decision);
        self::assertSame('2026-08-07T10:00:00.123456Z', $row['effective_at']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $row['revision_checksum']);
    }

    public function test_checksum_corruption_is_rejected(): void
    {
        $mapper = new ReliabilityOperationsOwnerSourceMapper;
        $row = $mapper->toRow(self::state(ReliabilityOperationsStream::Observability, 'available'));
        $row['revision_checksum'] = str_repeat('0', 64);
        $this->expectException(RuntimeException::class);
        $mapper->toState($row);
    }

    /** @return iterable<string, array{ReliabilityOperationsStream,string}> */
    public static function streams(): iterable
    {
        yield 'observability' => [ReliabilityOperationsStream::Observability, 'available'];
        yield 'health' => [ReliabilityOperationsStream::ServiceHealth, 'healthy'];
        yield 'alerting' => [ReliabilityOperationsStream::Alerting, 'ready'];
        yield 'continuity' => [ReliabilityOperationsStream::Continuity, 'ready'];
        yield 'maintenance' => [ReliabilityOperationsStream::MaintenanceOperations, 'ready'];
        yield 'capacity' => [ReliabilityOperationsStream::CapacityPlanning, 'sufficient'];
        yield 'readiness' => [ReliabilityOperationsStream::OperationalReadiness, 'ready'];
    }

    private static function state(ReliabilityOperationsStream $stream, string $decision): ReliabilityOperationsRevisionState
    {
        return new ReliabilityOperationsRevisionState('platform:primary', $stream, 1, $decision, new DateTimeImmutable('2026-08-07T12:00:00.123456+02:00'), new DateTimeImmutable('2026-08-07T12:00:01.123456+02:00'));
    }
}
