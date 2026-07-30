<?php

namespace Tests\Unit\LeadLifecycleHttpRuntime;

use App\Http\LeadLifecycleHttpResultMapper;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleDiagnostic;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleOrchestrationResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleOrchestrationStatus;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LeadLifecycleHttpResultMapperTest extends TestCase
{
    /** @return iterable<string, array{LeadLifecycleOrchestrationResult,int,string,?string}> */
    public static function results(): iterable
    {
        yield 'applied' => [new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::Applied), 200, 'applied', null];
        yield 'already applied' => [new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::AlreadyApplied), 200, 'already_applied', null];
        yield 'missing' => [new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::Missing), 404, 'missing', null];
        yield 'version conflict' => [new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::VersionConflict), 409, 'version_conflict', null];
        yield 'denied' => [new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::Denied, LeadLifecycleDiagnostic::TransitionForbidden), 422, 'denied', 'transition_forbidden'];
        yield 'state conflict' => [new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::StateConflict), 409, 'state_conflict', null];
        yield 'context divergence' => [new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::ContextDivergence), 409, 'context_divergence', null];
        yield 'persistence corrupted' => [new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::PersistenceCorrupted), 503, 'persistence_corrupted', null];
    }

    #[DataProvider('results')]
    public function test_all_results_have_a_closed_http_mapping(LeadLifecycleOrchestrationResult $result, int $http, string $status, ?string $diagnostic): void
    {
        $factory = $this->createMock(ResponseFactory::class);
        $factory->method('json')->willReturnCallback(static fn (array $data, int $code): JsonResponse => new JsonResponse($data, $code));
        Container::setInstance(new Container);
        Container::getInstance()->instance(ResponseFactory::class, $factory);

        $response = (new LeadLifecycleHttpResultMapper)->response($result);

        self::assertSame($http, $response->getStatusCode());
        self::assertSame(['status' => $status, 'diagnostic' => $diagnostic], $response->getData(true));
    }
}
