<?php

namespace Tests\Unit;

use App\Http\AdministrativeActionLifecycleHttpResultMapper;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleDiagnostic;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleOrchestrationResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleOrchestrationStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class AdministrativeActionLifecycleHttpResultMapperTest extends TestCase
{
    /** @return iterable<string, array{AdministrativeActionLifecycleOrchestrationStatus, int}> */
    public static function mappings(): iterable
    {
        yield 'applied' => [AdministrativeActionLifecycleOrchestrationStatus::Applied, 200];
        yield 'already applied' => [AdministrativeActionLifecycleOrchestrationStatus::AlreadyApplied, 200];
        yield 'missing' => [AdministrativeActionLifecycleOrchestrationStatus::Missing, 404];
        yield 'version conflict' => [AdministrativeActionLifecycleOrchestrationStatus::VersionConflict, 409];
        yield 'denied' => [AdministrativeActionLifecycleOrchestrationStatus::Denied, 422];
        yield 'state conflict' => [AdministrativeActionLifecycleOrchestrationStatus::StateConflict, 409];
        yield 'context divergence' => [AdministrativeActionLifecycleOrchestrationStatus::ContextDivergence, 409];
        yield 'transition divergence' => [AdministrativeActionLifecycleOrchestrationStatus::TransitionDivergence, 409];
        yield 'persistence corrupted' => [AdministrativeActionLifecycleOrchestrationStatus::PersistenceCorrupted, 503];
    }

    #[DataProvider('mappings')]
    public function test_it_maps_every_closed_result(
        AdministrativeActionLifecycleOrchestrationStatus $status,
        int $httpStatus,
    ): void {
        $diagnostic = $status === AdministrativeActionLifecycleOrchestrationStatus::Denied
            ? AdministrativeActionLifecycleDiagnostic::MissingReason
            : null;

        $response = (new AdministrativeActionLifecycleHttpResultMapper)->response(
            new AdministrativeActionLifecycleOrchestrationResult($status, $diagnostic),
        );

        self::assertSame($httpStatus, $response->getStatusCode());
        self::assertSame([
            'status' => $status->value,
            'diagnostic' => $diagnostic?->value,
        ], $response->getData(true));
    }
}
