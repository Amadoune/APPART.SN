<?php

namespace Tests\Feature;

use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\IdentityAccessSessionInspection;
use App\Application\ModerationHttp\Contract\ModerationHttpRuntimeV1;
use App\Application\ModerationHttp\ModerationHttpOperation;
use App\Application\ModerationHttp\ModerationHttpResult;
use App\Application\ModerationHttp\ModerationHttpStatus;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Illuminate\Cookie\Middleware\EncryptCookies;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ModerationHttpMutationAdaptationTest extends TestCase
{
    private RecordingModerationHttpRuntime $runtime;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.key', 'base64:'.base64_encode(str_repeat('h', 32)));
        $this->withoutMiddleware(EncryptCookies::class);
        $session = $this->createStub(IdentityAccessHttpRuntime::class);
        $session->method('inspectSession')->willReturn(
            IdentityAccessSessionInspection::valid(
                AccountId::fromString('71000000-0000-4000-8000-000000000001'),
            ),
        );
        $this->app->instance(IdentityAccessHttpRuntime::class, $session);
        $this->runtime = new RecordingModerationHttpRuntime;
        $this->app->instance(ModerationHttpRuntimeV1::class, $this->runtime);
    }

    /** @param array<string, mixed> $payload */
    #[Test]
    #[DataProvider('mutations')]
    public function each_mutation_reaches_exactly_one_certified_command_adapter(
        string $uri,
        ModerationHttpOperation $operation,
        array $payload,
    ): void {
        $response = $this->withCredentials()
            ->withUnencryptedCookie('__Host-appart_session', 'valid')
            ->withHeader('Idempotency-Key', '71000000-0000-4000-8000-000000000099')
            ->postJson($uri, $payload + [
                'occurredAt' => '2026-07-30T10:00:00+00:00',
                'policyVersion' => 'v1',
            ]);

        $response->assertOk()->assertJsonPath('status', 'succeeded');
        self::assertSame($operation, $this->runtime->operation);
        self::assertSame('71000000-0000-4000-8000-000000000001', $this->runtime->accountId);
        self::assertSame('71000000-0000-4000-8000-000000000099', $this->runtime->intentId);
        self::assertSame($payload['expectedVersion'] ?? null, $this->runtime->input['expectedVersion'] ?? null);
    }

    /** @return iterable<string, array{string, ModerationHttpOperation, array<string, mixed>}> */
    public static function mutations(): iterable
    {
        $id = static fn (int $suffix): string => sprintf('71000000-0000-4000-8000-%012d', $suffix);

        yield 'submit' => [
            '/api/moderation/v1/reports/'.$id(10),
            ModerationHttpOperation::SubmitReport,
            ['targetType' => 'Listing', 'targetId' => $id(11), 'category' => 'fraud', 'statementReference' => 'opaque'],
        ];
        yield 'validate' => [
            '/api/moderation/v1/reports/'.$id(10).'/validation',
            ModerationHttpOperation::ValidateReport,
            ['caseId' => $id(12), 'disposition' => 'Accepted', 'reasonCode' => 'verified', 'expectedVersion' => 1],
        ];
        yield 'finding' => [
            '/api/moderation/v1/cases/'.$id(12).'/findings',
            ModerationHttpOperation::RecordFinding,
            ['findingId' => $id(13), 'reportIds' => [$id(10)], 'findingCode' => 'confirmed', 'evidenceReferences' => ['opaque'], 'expectedVersion' => 2],
        ];
        yield 'decision' => [
            '/api/moderation/v1/cases/'.$id(12).'/decisions',
            ModerationHttpOperation::IssueDecision,
            ['decisionId' => $id(14), 'findingIds' => [$id(13)], 'disposition' => 'Confirmed', 'targetAction' => 'None', 'expectedVersion' => 3],
        ];
        yield 'close' => [
            '/api/moderation/v1/cases/'.$id(12).'/closure',
            ModerationHttpOperation::CloseCase,
            ['closureCode' => 'resolved', 'expectedVersion' => 4],
        ];
        yield 'claim' => [
            '/api/moderation/v1/queue/'.$id(15).'/claim',
            ModerationHttpOperation::ClaimQueueItem,
            ['leaseId' => $id(16), 'leaseExpiresAt' => '2026-07-30T10:05:00+00:00'],
        ];
    }
}

final class RecordingModerationHttpRuntime implements ModerationHttpRuntimeV1
{
    public ?ModerationHttpOperation $operation = null;

    public ?string $accountId = null;

    public ?string $intentId = null;

    /** @var array<string, mixed> */
    public array $input = [];

    public function execute(
        ModerationHttpOperation $operation,
        string $accountId,
        ?string $resourceId,
        ?string $intentId,
        array $input,
    ): ModerationHttpResult {
        $this->operation = $operation;
        $this->accountId = $accountId;
        $this->intentId = $intentId;
        $this->input = $input;

        return new ModerationHttpResult(ModerationHttpStatus::Succeeded);
    }
}
