<?php

namespace Tests\Feature;

use App\Application\IdentityAccessHttp\AuthenticatedSessionContext;
use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\IdentityAccessSessionInspection;
use App\Application\ModerationHttp\Contract\ModerationHttpRuntimeV1;
use App\Application\ModerationHttp\ModerationHttpOperation;
use App\Application\ModerationHttp\ModerationHttpResult;
use App\Application\ModerationHttp\ModerationHttpStatus;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Tests\TestCase;

final class ModerationHttpSecurityTest extends TestCase
{
    private const ACCOUNT = '70000000-0000-4000-8000-000000000001';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.key', 'base64:'.base64_encode(str_repeat('m', 32)));
        $this->withoutMiddleware(EncryptCookies::class);
        $session = $this->createStub(IdentityAccessHttpRuntime::class);
        $session->method('inspectSession')->willReturnCallback(
            static fn (string $secret, DateTimeImmutable $at): IdentityAccessSessionInspection => $secret === 'valid'
                ? IdentityAccessSessionInspection::valid(new AuthenticatedSessionContext(AccountId::fromString(self::ACCOUNT), '70000000-0000-4000-8000-000000000002'))
                : IdentityAccessSessionInspection::invalid(),
        );
        $this->app->instance(IdentityAccessHttpRuntime::class, $session);
        $this->app->instance(ModerationHttpRuntimeV1::class, new class implements ModerationHttpRuntimeV1
        {
            public function execute(
                ModerationHttpOperation $operation,
                string $accountId,
                ?string $resourceId,
                ?string $intentId,
                array $input,
            ): ModerationHttpResult {
                return new ModerationHttpResult(ModerationHttpStatus::Succeeded, [
                    'accountId' => $accountId,
                    'operation' => $operation->value,
                ]);
            }
        });
    }

    public function test_private_read_is_session_scoped_and_has_security_headers(): void
    {
        $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'valid')
            ->getJson('/api/moderation/v1/reports/70000000-0000-4000-8000-000000000002')
            ->assertOk()
            ->assertJsonPath('accountId', self::ACCOUNT)
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_missing_session_is_fail_closed(): void
    {
        $this->getJson('/api/moderation/v1/queue?state=Available&limit=25')
            ->assertUnauthorized()
            ->assertJsonPath('status', 'authentication_required');
    }

    public function test_mutation_requires_idempotency_key_and_unknown_fields_are_rejected(): void
    {
        $payload = [
            'targetType' => 'Listing',
            'targetId' => '70000000-0000-4000-8000-000000000003',
            'category' => 'fraud',
            'statementReference' => 'opaque:evidence',
            'occurredAt' => '2026-07-30T10:00:00+00:00',
            'policyVersion' => 'v1',
        ];
        $url = '/api/moderation/v1/reports/70000000-0000-4000-8000-000000000004';

        $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'valid')
            ->postJson($url, $payload)
            ->assertUnprocessable();

        $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'valid')
            ->withHeader('Idempotency-Key', '70000000-0000-4000-8000-000000000005')
            ->postJson($url, $payload + ['unexpected' => true])
            ->assertUnprocessable();
    }

    public function test_internal_failure_is_a_closed_response_without_diagnostics(): void
    {
        $this->app->instance(ModerationHttpRuntimeV1::class, new class implements ModerationHttpRuntimeV1
        {
            public function execute(
                ModerationHttpOperation $operation,
                string $accountId,
                ?string $resourceId,
                ?string $intentId,
                array $input,
            ): ModerationHttpResult {
                throw new \RuntimeException('private SQL diagnostic');
            }
        });

        $response = $this->withCredentials()
            ->withUnencryptedCookie('__Host-appart_session', 'valid')
            ->getJson('/api/moderation/v1/reports/70000000-0000-4000-8000-000000000002')
            ->assertStatus(503)
            ->assertExactJson(['status' => 'unavailable']);

        self::assertStringNotContainsString('SQL', $response->getContent());
        self::assertStringNotContainsString('diagnostic', $response->getContent());
    }
}
