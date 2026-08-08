<?php

namespace Tests\Unit\ReliabilityOperations\Runtime;

use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsOwnerSource;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsReadResult;
use Appart\Modules\ReliabilityOperations\Application\Runtime\DeterministicReliabilityOperationsRuntime;
use Appart\Modules\ReliabilityOperations\Application\Runtime\DeterministicReliabilityOperationsRuntimeAvailabilityPolicy;
use Appart\Modules\ReliabilityOperations\Application\Runtime\ReliabilityOperationsRuntimeAvailability;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ReliabilityOperationsRuntimeTest extends TestCase
{
    public function test_found_and_missing_are_technically_available(): void
    {
        $source = $this->createMock(ReliabilityOperationsOwnerSource::class);
        $source->method('read')->willReturn(ReliabilityOperationsReadResult::missing());
        self::assertSame(ReliabilityOperationsRuntimeAvailability::Available, (new DeterministicReliabilityOperationsRuntimeAvailabilityPolicy($source))->inspect());
    }

    public function test_corruption_and_dependency_failure_are_reduced_deterministically(): void
    {
        $corrupted = $this->createMock(ReliabilityOperationsOwnerSource::class);
        $corrupted->method('read')->willReturn(ReliabilityOperationsReadResult::corrupted());
        self::assertSame(ReliabilityOperationsRuntimeAvailability::Corrupted, (new DeterministicReliabilityOperationsRuntimeAvailabilityPolicy($corrupted))->inspect());

        $dependency = $this->createMock(ReliabilityOperationsOwnerSource::class);
        $dependency->method('read')->willReturn(ReliabilityOperationsReadResult::dependencyUnavailable());
        self::assertSame(ReliabilityOperationsRuntimeAvailability::DependencyUnavailable, (new DeterministicReliabilityOperationsRuntimeAvailabilityPolicy($dependency))->inspect());
    }

    public function test_exception_is_dependency_unavailable_and_diagnostics_are_minimal(): void
    {
        $source = $this->createMock(ReliabilityOperationsOwnerSource::class);
        $source->method('read')->willThrowException(new RuntimeException('technical'));
        $runtime = new DeterministicReliabilityOperationsRuntime(new DeterministicReliabilityOperationsRuntimeAvailabilityPolicy($source));
        self::assertSame(ReliabilityOperationsRuntimeAvailability::DependencyUnavailable, $runtime->availability());
        self::assertSame([
            'runtimeId' => 'reliability-operations.owner-source',
            'version' => 'reliability-operations-runtime-v1',
            'availability' => ReliabilityOperationsRuntimeAvailability::DependencyUnavailable,
        ], get_object_vars($runtime->diagnostics()));
    }
}
