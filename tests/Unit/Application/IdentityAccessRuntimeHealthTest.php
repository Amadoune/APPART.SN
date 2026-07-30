<?php

namespace Tests\Unit\Application;

use Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime\DeterministicIdentityAccessRuntimeHealthInspector;
use Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime\IdentityAccessRuntimeComponent;
use Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime\IdentityAccessRuntimeHealthStatus;
use Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime\IdentityAccessRuntimeRegistration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdentityAccessRuntimeHealthTest extends TestCase
{
    #[Test]
    public function all_required_components_are_healthy(): void
    {
        $registrations = array_map(
            static fn (IdentityAccessRuntimeComponent $component): IdentityAccessRuntimeRegistration => new IdentityAccessRuntimeRegistration($component, true, true),
            IdentityAccessRuntimeComponent::cases(),
        );

        $report = (new DeterministicIdentityAccessRuntimeHealthInspector($registrations))->inspect();

        self::assertSame(IdentityAccessRuntimeHealthStatus::Healthy, $report->status);
        self::assertSame([], $report->unhealthyComponents);
    }

    #[Test]
    public function a_missing_or_incompatible_component_is_fail_closed(): void
    {
        $report = (new DeterministicIdentityAccessRuntimeHealthInspector([
            new IdentityAccessRuntimeRegistration(IdentityAccessRuntimeComponent::AccountRegistry, true, true),
            new IdentityAccessRuntimeRegistration(IdentityAccessRuntimeComponent::AccountAvailability, true, false),
        ]))->inspect();

        self::assertSame(IdentityAccessRuntimeHealthStatus::Unhealthy, $report->status);
        self::assertSame([IdentityAccessRuntimeComponent::AccountAvailability], $report->unhealthyComponents);
    }
}
