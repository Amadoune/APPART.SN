<?php

namespace Tests\Unit\ReliabilityOperations\OwnerReader;

use Appart\Modules\ReliabilityOperations\Application\OwnerReader\AlertingOwnerReader;
use Appart\Modules\ReliabilityOperations\Application\OwnerReader\CapacityPlanningOwnerReader;
use Appart\Modules\ReliabilityOperations\Application\OwnerReader\ContinuityOwnerReader;
use Appart\Modules\ReliabilityOperations\Application\OwnerReader\MaintenanceOperationsOwnerReader;
use Appart\Modules\ReliabilityOperations\Application\OwnerReader\ObservabilityOwnerReader;
use Appart\Modules\ReliabilityOperations\Application\OwnerReader\OperationalReadinessOwnerReader;
use Appart\Modules\ReliabilityOperations\Application\OwnerReader\ServiceHealthOwnerReader;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsOwnerSource;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsReadResult;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsRevisionState;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsStream;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\AlertingReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\CapacityPlanningReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ContinuityReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\MaintenanceOperationsReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ObservabilityReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\OperationalReadinessReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ServiceHealthReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReliabilityOperationsOwnerReaderTest extends TestCase
{
    #[DataProvider('readers')]
    public function test_found_decision_is_reduced_homonymously(string $reader, ReliabilityOperationsStream $stream, string $decision): void
    {
        $source = $this->source(ReliabilityOperationsReadResult::found(new ReliabilityOperationsRevisionState('platform:primary', $stream, 1, $decision, self::at(), self::at())));
        $result = $this->reader($reader, $source)->read(self::observedAt());
        self::assertSame($decision, $result->status->value);
        self::assertSame('2026-08-07T10:00:00.123456Z', $result->observedAt);
        self::assertSame(['observedAt', 'status'], array_keys(get_object_vars($result)));
    }

    #[DataProvider('readers')]
    public function test_structural_states_are_reduced_homonymously(string $reader, ReliabilityOperationsStream $stream, string $decision): void
    {
        unset($stream, $decision);
        foreach ([
            [ReliabilityOperationsReadResult::missing(), 'missing'],
            [ReliabilityOperationsReadResult::corrupted(), 'corrupted'],
            [ReliabilityOperationsReadResult::dependencyUnavailable(), 'dependency_unavailable'],
        ] as [$sourceResult, $expected]) {
            $result = $this->reader($reader, $this->source($sourceResult))->read(self::observedAt());
            self::assertSame($expected, $result->status->value);
        }
    }

    /** @return iterable<string, array{string,ReliabilityOperationsStream,string}> */
    public static function readers(): iterable
    {
        yield 'observability' => ['observability', ReliabilityOperationsStream::Observability, 'available'];
        yield 'health' => ['health', ReliabilityOperationsStream::ServiceHealth, 'healthy'];
        yield 'alerting' => ['alerting', ReliabilityOperationsStream::Alerting, 'ready'];
        yield 'maintenance' => ['maintenance', ReliabilityOperationsStream::MaintenanceOperations, 'ready'];
        yield 'continuity' => ['continuity', ReliabilityOperationsStream::Continuity, 'ready'];
        yield 'capacity' => ['capacity', ReliabilityOperationsStream::CapacityPlanning, 'sufficient'];
        yield 'readiness' => ['readiness', ReliabilityOperationsStream::OperationalReadiness, 'ready'];
    }

    private function source(ReliabilityOperationsReadResult $result): ReliabilityOperationsOwnerSource
    {
        $source = $this->createMock(ReliabilityOperationsOwnerSource::class);
        $source->method('read')->willReturn($result);

        return $source;
    }

    private function reader(string $reader, ReliabilityOperationsOwnerSource $source): ObservabilityReaderV1|ServiceHealthReaderV1|AlertingReaderV1|MaintenanceOperationsReaderV1|ContinuityReaderV1|CapacityPlanningReaderV1|OperationalReadinessReaderV1
    {
        return match ($reader) {
            'observability' => new ObservabilityOwnerReader($source),
            'health' => new ServiceHealthOwnerReader($source),
            'alerting' => new AlertingOwnerReader($source),
            'maintenance' => new MaintenanceOperationsOwnerReader($source),
            'continuity' => new ContinuityOwnerReader($source),
            'capacity' => new CapacityPlanningOwnerReader($source),
            'readiness' => new OperationalReadinessOwnerReader($source),
            default => throw new InvalidArgumentException('Unknown Reliability Operations reader.'),
        };
    }

    private static function observedAt(): ReliabilityOperationsObservedAt
    {
        return new ReliabilityOperationsObservedAt(self::at());
    }

    private static function at(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-07T10:00:00.123456Z');
    }
}
