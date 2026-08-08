<?php

namespace Tests\Unit\AdministrationConsole\Runtime;

use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationAuditReadResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationConsoleOwnerSource;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationOperatorReadResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationQueueReadResult;
use Appart\Modules\AdministrationConsole\Application\Runtime\AdministrationConsoleRuntimeAvailability;
use Appart\Modules\AdministrationConsole\Application\Runtime\DeterministicAdministrationConsoleRuntime;
use Appart\Modules\AdministrationConsole\Application\Runtime\DeterministicAdministrationConsoleRuntimeAvailabilityPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AdministrationConsoleRuntimeTest extends TestCase
{
    #[DataProvider('availabilityCases')]
    public function test_policy_reduces_owner_source_results_mechanically(AdministrationOperatorReadResult $operator, AdministrationQueueReadResult $queue, AdministrationAuditReadResult $audit, AdministrationConsoleRuntimeAvailability $expected): void
    {
        $source = $this->createMock(AdministrationConsoleOwnerSource::class);
        $source->method('readOperator')->willReturn($operator);
        $source->method('readQueue')->willReturn($queue);
        $source->method('readAudit')->willReturn($audit);

        self::assertSame($expected, (new DeterministicAdministrationConsoleRuntimeAvailabilityPolicy($source))->inspect());
    }

    /** @return iterable<string, array{AdministrationOperatorReadResult, AdministrationQueueReadResult, AdministrationAuditReadResult, AdministrationConsoleRuntimeAvailability}> */
    public static function availabilityCases(): iterable
    {
        yield 'missing is technically available' => [AdministrationOperatorReadResult::missing(), AdministrationQueueReadResult::missing(), AdministrationAuditReadResult::missing(), AdministrationConsoleRuntimeAvailability::Available];
        yield 'corrupted is fail closed' => [AdministrationOperatorReadResult::corrupted(), AdministrationQueueReadResult::missing(), AdministrationAuditReadResult::missing(), AdministrationConsoleRuntimeAvailability::Corrupted];
        yield 'dependency failure has priority' => [AdministrationOperatorReadResult::corrupted(), AdministrationQueueReadResult::dependencyUnavailable(), AdministrationAuditReadResult::missing(), AdministrationConsoleRuntimeAvailability::DependencyUnavailable];
    }

    public function test_exception_is_dependency_unavailable_and_diagnostics_are_closed(): void
    {
        $source = $this->createMock(AdministrationConsoleOwnerSource::class);
        $source->method('readOperator')->willThrowException(new RuntimeException('technical'));
        $runtime = new DeterministicAdministrationConsoleRuntime(new DeterministicAdministrationConsoleRuntimeAvailabilityPolicy($source));

        self::assertSame(AdministrationConsoleRuntimeAvailability::DependencyUnavailable, $runtime->availability());
        self::assertSame([
            'runtimeId' => 'administration-console.owner-source',
            'version' => 'administration-console-runtime-v1',
            'availability' => AdministrationConsoleRuntimeAvailability::DependencyUnavailable,
        ], get_object_vars($runtime->diagnostics()));
    }
}
