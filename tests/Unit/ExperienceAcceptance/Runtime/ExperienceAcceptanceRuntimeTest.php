<?php

namespace Tests\Unit\ExperienceAcceptance\Runtime;

use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceOwnerSource;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceReadResult;
use Appart\Modules\ExperienceAcceptance\Application\Runtime\DeterministicExperienceAcceptanceRuntime;
use Appart\Modules\ExperienceAcceptance\Application\Runtime\DeterministicExperienceAcceptanceRuntimeAvailabilityPolicy;
use Appart\Modules\ExperienceAcceptance\Application\Runtime\ExperienceAcceptanceRuntimeAvailability;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExperienceAcceptanceRuntimeTest extends TestCase
{
    public function test_found_and_missing_are_technically_available(): void
    {
        $source = $this->createMock(ExperienceAcceptanceOwnerSource::class);
        $source->method('read')->willReturn(ExperienceAcceptanceReadResult::missing());
        self::assertSame(ExperienceAcceptanceRuntimeAvailability::Available, (new DeterministicExperienceAcceptanceRuntimeAvailabilityPolicy($source))->inspect());
    }

    public function test_corruption_and_dependency_failure_are_reduced_deterministically(): void
    {
        $corrupted = $this->createMock(ExperienceAcceptanceOwnerSource::class);
        $corrupted->method('read')->willReturn(ExperienceAcceptanceReadResult::corrupted());
        self::assertSame(ExperienceAcceptanceRuntimeAvailability::Corrupted, (new DeterministicExperienceAcceptanceRuntimeAvailabilityPolicy($corrupted))->inspect());

        $dependency = $this->createMock(ExperienceAcceptanceOwnerSource::class);
        $dependency->method('read')->willReturn(ExperienceAcceptanceReadResult::dependencyUnavailable());
        self::assertSame(ExperienceAcceptanceRuntimeAvailability::DependencyUnavailable, (new DeterministicExperienceAcceptanceRuntimeAvailabilityPolicy($dependency))->inspect());
    }

    public function test_exception_is_dependency_unavailable_and_diagnostics_are_minimal(): void
    {
        $source = $this->createMock(ExperienceAcceptanceOwnerSource::class);
        $source->method('read')->willThrowException(new RuntimeException('technical'));
        $runtime = new DeterministicExperienceAcceptanceRuntime(new DeterministicExperienceAcceptanceRuntimeAvailabilityPolicy($source));
        self::assertSame(ExperienceAcceptanceRuntimeAvailability::DependencyUnavailable, $runtime->availability());
        self::assertSame([
            'runtimeId' => 'experience-acceptance.owner-source',
            'version' => 'experience-acceptance-runtime-v1',
            'availability' => ExperienceAcceptanceRuntimeAvailability::DependencyUnavailable,
        ], get_object_vars($runtime->diagnostics()));
    }
}
