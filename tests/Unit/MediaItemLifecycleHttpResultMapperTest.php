<?php

namespace Tests\Unit;

use App\Http\MediaItemLifecycleHttpResultMapper;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleDiagnostic;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleOrchestrationResult;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleOrchestrationStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class MediaItemLifecycleHttpResultMapperTest extends TestCase
{
    /** @return iterable<string, array{MediaItemLifecycleOrchestrationStatus, int}> */
    public static function mappings(): iterable
    {
        yield 'applied' => [MediaItemLifecycleOrchestrationStatus::Applied, 200];
        yield 'already applied' => [MediaItemLifecycleOrchestrationStatus::AlreadyApplied, 200];
        yield 'missing' => [MediaItemLifecycleOrchestrationStatus::Missing, 404];
        yield 'version conflict' => [MediaItemLifecycleOrchestrationStatus::VersionConflict, 409];
        yield 'denied' => [MediaItemLifecycleOrchestrationStatus::Denied, 422];
        yield 'state conflict' => [MediaItemLifecycleOrchestrationStatus::StateConflict, 409];
        yield 'context divergence' => [MediaItemLifecycleOrchestrationStatus::ContextDivergence, 409];
        yield 'persistence corrupted' => [MediaItemLifecycleOrchestrationStatus::PersistenceCorrupted, 503];
    }

    #[DataProvider('mappings')]
    public function test_it_maps_every_closed_result(MediaItemLifecycleOrchestrationStatus $status, int $httpStatus): void
    {
        $diagnostic = $status === MediaItemLifecycleOrchestrationStatus::Denied
            ? MediaItemLifecycleDiagnostic::TerminalState
            : null;

        $response = (new MediaItemLifecycleHttpResultMapper)->response(new MediaItemLifecycleOrchestrationResult($status, $diagnostic));

        self::assertSame($httpStatus, $response->getStatusCode());
        self::assertSame([
            'status' => $status->value,
            'diagnostic' => $diagnostic?->value,
        ], $response->getData(true));
    }
}
