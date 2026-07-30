<?php

namespace Tests\Unit;

use App\Http\ProfessionalStatusHttpResultMapper;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusDiagnostic;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusOrchestrationResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusOrchestrationStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ProfessionalStatusHttpResultMapperTest extends TestCase
{
    /** @return iterable<string, array{ProfessionalStatusOrchestrationStatus, int}> */
    public static function mappings(): iterable
    {
        yield 'applied' => [ProfessionalStatusOrchestrationStatus::Applied, 200];
        yield 'already applied' => [ProfessionalStatusOrchestrationStatus::AlreadyApplied, 200];
        yield 'missing' => [ProfessionalStatusOrchestrationStatus::Missing, 404];
        yield 'version conflict' => [ProfessionalStatusOrchestrationStatus::VersionConflict, 409];
        yield 'denied' => [ProfessionalStatusOrchestrationStatus::Denied, 422];
        yield 'state conflict' => [ProfessionalStatusOrchestrationStatus::StateConflict, 409];
        yield 'context divergence' => [ProfessionalStatusOrchestrationStatus::ContextDivergence, 409];
        yield 'persistence corrupted' => [ProfessionalStatusOrchestrationStatus::PersistenceCorrupted, 503];
    }

    #[DataProvider('mappings')]
    public function test_it_maps_every_closed_result(ProfessionalStatusOrchestrationStatus $status, int $httpStatus): void
    {
        $diagnostic = $status === ProfessionalStatusOrchestrationStatus::Denied
            ? ProfessionalStatusDiagnostic::IncompatibleState
            : null;

        $response = (new ProfessionalStatusHttpResultMapper)->response(
            new ProfessionalStatusOrchestrationResult($status, $diagnostic),
        );

        self::assertSame($httpStatus, $response->getStatusCode());
        self::assertSame([
            'status' => $status->value,
            'diagnostic' => $diagnostic?->value,
        ], $response->getData(true));
    }
}
