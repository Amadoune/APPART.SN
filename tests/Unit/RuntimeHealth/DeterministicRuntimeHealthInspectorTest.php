<?php

namespace Tests\Unit\RuntimeHealth;

use App\Application\RuntimeHealth\DeterministicRuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthComponent;
use App\Application\RuntimeHealth\RuntimeHealthDiagnosticCode;
use App\Application\RuntimeHealth\RuntimeHealthRegistration;
use App\Application\RuntimeHealth\RuntimeHealthRequirement;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use PHPUnit\Framework\TestCase;

final class DeterministicRuntimeHealthInspectorTest extends TestCase
{
    public function test_declared_concrete_capability_is_compatible_without_execution(): void
    {
        $requirement = new RuntimeHealthRequirement(RuntimeHealthComponent::ListingPublicationWorkflow, HealthTestImplementation::class);
        $implementation = new HealthTestImplementation;

        $result = new DeterministicRuntimeHealthInspector(
            [$requirement],
            [new RuntimeHealthRegistration(RuntimeHealthComponent::ListingPublicationWorkflow, true, $implementation)],
        )->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $result->status);
        self::assertSame([], $result->diagnostics);
    }

    public function test_certified_runtime_is_healthy_without_executing_any_component(): void
    {
        $requirements = PublicProjectionRuntimeRequirements::certified();
        $registrations = array_map(fn (RuntimeHealthRequirement $requirement): RuntimeHealthRegistration => new RuntimeHealthRegistration(
            $requirement->component,
            true,
            interface_exists($requirement->contract)
                ? $this->createMock($requirement->contract)
                : (new \ReflectionClass($requirement->contract))->newInstanceWithoutConstructor(),
        ), $requirements);

        $result = (new DeterministicRuntimeHealthInspector($requirements, $registrations))->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $result->status);
        self::assertSame([], $result->diagnostics);
        self::assertCount(60, $requirements);
    }

    public function test_absent_required_component_makes_runtime_unavailable(): void
    {
        $requirement = new RuntimeHealthRequirement(RuntimeHealthComponent::ProjectionUpdater, HealthTestPort::class);
        $result = (new DeterministicRuntimeHealthInspector([$requirement], []))->inspect();

        self::assertSame(RuntimeHealthStatus::Unavailable, $result->status);
        self::assertSame(RuntimeHealthDiagnosticCode::ComponentAbsent, $result->diagnostics[0]->code);
    }

    public function test_unregistered_invalid_incompatible_and_missing_dependency_are_distinct(): void
    {
        $requirements = [
            new RuntimeHealthRequirement(RuntimeHealthComponent::ProjectionUpdater, HealthTestPort::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ProjectionStore, HealthTestPort::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ProjectionRuntimeSource, HealthTestPort::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::DeliveryConsumer, HealthTestPort::class),
        ];
        $registrations = [
            new RuntimeHealthRegistration(RuntimeHealthComponent::ProjectionUpdater, false, null),
            new RuntimeHealthRegistration(RuntimeHealthComponent::ProjectionStore, true, new HealthTestImplementation, false),
            new RuntimeHealthRegistration(RuntimeHealthComponent::ProjectionRuntimeSource, true, new \stdClass),
            new RuntimeHealthRegistration(RuntimeHealthComponent::DeliveryConsumer, true, new HealthTestImplementation, missingDependencies: [RuntimeHealthComponent::ProjectionSourceLookup]),
        ];
        $result = (new DeterministicRuntimeHealthInspector($requirements, $registrations))->inspect();
        $codes = array_map(static fn ($diagnostic) => $diagnostic->code, $result->diagnostics);

        self::assertContains(RuntimeHealthDiagnosticCode::ImplementationNotRegistered, $codes);
        self::assertContains(RuntimeHealthDiagnosticCode::InvalidConfiguration, $codes);
        self::assertContains(RuntimeHealthDiagnosticCode::IncompatibleContract, $codes);
        self::assertContains(RuntimeHealthDiagnosticCode::DependencyMissing, $codes);
    }

    public function test_optional_anomaly_is_degraded_and_diagnostics_have_stable_order(): void
    {
        $requirements = [
            new RuntimeHealthRequirement(RuntimeHealthComponent::ProjectionStore, HealthTestPort::class, false),
            new RuntimeHealthRequirement(RuntimeHealthComponent::DeliveryConsumer, HealthTestPort::class, false),
        ];
        $inspector = new DeterministicRuntimeHealthInspector($requirements, []);
        $first = $inspector->inspect();
        $second = $inspector->inspect();

        self::assertSame(RuntimeHealthStatus::Degraded, $first->status);
        self::assertEquals($first, $second);
        self::assertSame([RuntimeHealthComponent::DeliveryConsumer, RuntimeHealthComponent::ProjectionStore], array_map(static fn ($diagnostic) => $diagnostic->component, $first->diagnostics));
    }
}

interface HealthTestPort {}

final class HealthTestImplementation implements HealthTestPort {}
