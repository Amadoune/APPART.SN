<?php

namespace Tests\Feature;

use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\IdentityAccessHttpCommand;
use App\Application\IdentityAccessHttp\IdentityAccessHttpResult;
use App\Application\IdentityAccessHttp\IdentityAccessSessionInspection;
use App\Application\PublicAuthoringIntegration\Contract\PublicAuthoringJourney;
use App\Application\PublicAuthoringIntegration\PublicAuthoringJourneyRequest;
use App\Application\PublicAuthoringIntegration\PublicAuthoringJourneyResponse;
use App\Application\PublicAuthoringIntegration\PublicAuthoringJourneyStatus;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Tests\TestCase;

final class PublicAuthoringIntegrationSecurityTest extends TestCase
{
    public const string ACCOUNT = '75000000-0000-4000-8000-000000000001';

    private const string PROPERTY = '75000000-0000-4000-8000-000000000002';

    private const string INTENT = '75000000-0000-4000-8000-000000000003';

    private CapturingPublicAuthoringJourney $journey;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('p', 32))]);
        $this->withoutMiddleware(EncryptCookies::class);
        $this->app->instance(IdentityAccessHttpRuntime::class, new class implements IdentityAccessHttpRuntime
        {
            public function execute(IdentityAccessHttpCommand $command): IdentityAccessHttpResult
            {
                throw new \LogicException('Not used.');
            }

            public function inspectSession(string $secret, DateTimeImmutable $at): IdentityAccessSessionInspection
            {
                return $secret === 'public-authoring-session'
                    ? IdentityAccessSessionInspection::valid(AccountId::fromString(PublicAuthoringIntegrationSecurityTest::ACCOUNT))
                    : IdentityAccessSessionInspection::invalid();
            }
        });
        $this->journey = new CapturingPublicAuthoringJourney;
        $this->app->instance(PublicAuthoringJourney::class, $this->journey);
    }

    public function test_api_and_workspace_require_the_certified_session(): void
    {
        $this->postJson('/api/public-authoring/v1/initiate-property', [])->assertUnauthorized();
        $this->get('/authoring/workspace')->assertUnauthorized();
    }

    public function test_public_request_is_auto_scoped_and_never_accepts_account_id(): void
    {
        $payload = $this->propertyPayload();
        $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'public-authoring-session')
            ->withHeader('Idempotency-Key', self::INTENT)
            ->postJson('/api/public-authoring/v1/initiate-property', $payload)
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertJson(['status' => 'succeeded', 'version' => 1]);

        self::assertSame(self::ACCOUNT, $this->journey->last->accountId);
        self::assertSame(self::PROPERTY, $this->journey->last->propertyId);

        $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'public-authoring-session')
            ->withHeader('Idempotency-Key', self::INTENT)
            ->postJson('/api/public-authoring/v1/initiate-property', $payload + ['accountId' => self::ACCOUNT])
            ->assertUnprocessable();
    }

    public function test_idempotency_and_unknown_operations_fail_closed(): void
    {
        $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'public-authoring-session')
            ->postJson('/api/public-authoring/v1/initiate-property', $this->propertyPayload())
            ->assertUnprocessable();

        $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'public-authoring-session')
            ->withHeader('Idempotency-Key', self::INTENT)
            ->postJson('/api/public-authoring/v1/delete-everything', $this->propertyPayload())
            ->assertNotFound();
    }

    /** @return array<string, mixed> */
    private function propertyPayload(): array
    {
        return [
            'propertyId' => self::PROPERTY,
            'expectedVersion' => 0,
            'requestedAt' => '2026-07-27T20:00:00.000000Z',
        ];
    }
}

final class CapturingPublicAuthoringJourney implements PublicAuthoringJourney
{
    public PublicAuthoringJourneyRequest $last;

    public function execute(PublicAuthoringJourneyRequest $request): PublicAuthoringJourneyResponse
    {
        $this->last = $request;

        return new PublicAuthoringJourneyResponse(PublicAuthoringJourneyStatus::Succeeded, ['version' => 1]);
    }
}
