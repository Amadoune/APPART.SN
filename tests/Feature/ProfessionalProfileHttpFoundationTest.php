<?php

namespace Tests\Feature;

use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\IdentityAccessHttpCommand;
use App\Application\IdentityAccessHttp\IdentityAccessHttpResult;
use App\Application\IdentityAccessHttp\IdentityAccessHttpStatus;
use App\Application\IdentityAccessHttp\IdentityAccessSessionInspection;
use App\Application\ProfessionalEndpoint\Contract\ProfessionalEndpointRuntimeV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\ProfessionalMandateResolutionV1;
use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\ProfessionalPublicStatusDecisionV1;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use DateTimeImmutable;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProfessionalProfileHttpFoundationTest extends TestCase
{
    private FakeProfessionalProfileHttpRuntime $runtime;

    protected function setUp(): void
    {
        parent::setUp();
        $this->runtime = new FakeProfessionalProfileHttpRuntime;
        $this->app->instance(ProfessionalEndpointRuntimeV1::class, $this->runtime);
        $this->app->instance(IdentityAccessHttpRuntime::class, new ProfessionalHttpSessionRuntime);
        config()->set('app.key', 'base64:'.base64_encode(str_repeat('p', 32)));
        $this->withoutMiddleware(EncryptCookies::class);
    }

    #[Test]
    public function both_endpoints_require_the_certified_iam_session(): void
    {
        $this->getJson('/professional/mandate')->assertStatus(401);
        $this->getJson('/professional/status')->assertStatus(401);
    }

    #[Test]
    public function mandate_endpoint_exposes_only_the_closed_public_result(): void
    {
        $this->runtime->mandateResult = ProfessionalMandateResolutionV1::resolved(
            ProfessionalId::fromString(
                '63000000-0000-4000-8000-000000000305',
            ),
        );

        $response = $this->authenticated('/professional/mandate');

        $response->assertOk()->assertExactJson([
            'status' => 'resolved',
            'professionalId' => '63000000-0000-4000-8000-000000000305',
        ])->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        self::assertSame('63000000-0000-4000-8000-000000000306', $this->runtime->lastAccount?->value);
    }

    #[Test]
    public function status_endpoint_maps_every_closed_decision_deterministically(): void
    {
        $statuses = [
            ProfessionalPublicStatusDecisionV1::Available->value => 200,
            ProfessionalPublicStatusDecisionV1::Unavailable->value => 409,
            ProfessionalPublicStatusDecisionV1::Missing->value => 404,
            ProfessionalPublicStatusDecisionV1::Corrupted->value => 500,
            ProfessionalPublicStatusDecisionV1::DependencyUnavailable->value => 503,
        ];

        foreach (ProfessionalPublicStatusDecisionV1::cases() as $decision) {
            $this->runtime->statusResult = $decision;
            $this->authenticated('/professional/status')
                ->assertStatus($statuses[$decision->value])
                ->assertExactJson(['status' => $decision->value]);
        }
    }

    private function authenticated(string $uri): TestResponse
    {
        return $this->withCredentials()
            ->withUnencryptedCookie('__Host-appart_session', 'valid-secret')
            ->getJson($uri);
    }
}

final class FakeProfessionalProfileHttpRuntime implements ProfessionalEndpointRuntimeV1
{
    public ProfessionalMandateResolutionV1 $mandateResult;

    public ProfessionalPublicStatusDecisionV1 $statusResult = ProfessionalPublicStatusDecisionV1::DependencyUnavailable;

    public ?AccountId $lastAccount = null;

    public function __construct()
    {
        $this->mandateResult = ProfessionalMandateResolutionV1::notMandated();
    }

    public function mandate(AccountId $accountId): ProfessionalMandateResolutionV1
    {
        $this->lastAccount = $accountId;

        return $this->mandateResult;
    }

    public function status(AccountId $accountId): ProfessionalPublicStatusDecisionV1
    {
        $this->lastAccount = $accountId;

        return $this->statusResult;
    }
}

final class ProfessionalHttpSessionRuntime implements IdentityAccessHttpRuntime
{
    public function execute(IdentityAccessHttpCommand $command): IdentityAccessHttpResult
    {
        return new IdentityAccessHttpResult(IdentityAccessHttpStatus::Unavailable);
    }

    public function inspectSession(string $secret, DateTimeImmutable $at): IdentityAccessSessionInspection
    {
        return $secret === 'valid-secret'
            ? IdentityAccessSessionInspection::valid(AccountId::fromString('63000000-0000-4000-8000-000000000306'))
            : IdentityAccessSessionInspection::invalid();
    }
}
