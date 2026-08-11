<?php

namespace Tests\Feature;

use App\Application\IdentityAccessHttp\AuthenticatedSessionContext;
use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\IdentityAccessHttpCommand;
use App\Application\IdentityAccessHttp\IdentityAccessHttpResult;
use App\Application\IdentityAccessHttp\IdentityAccessSessionInspection;
use App\Application\PropertyListingAuthoringHttp\Contract\PropertyListingAuthoringHttpRuntime;
use App\Application\PropertyListingAuthoringHttp\PropertyListingAuthoringHttpOperation;
use App\Application\PropertyListingAuthoringHttp\PropertyListingAuthoringHttpResult;
use App\Application\PropertyListingAuthoringHttp\PropertyListingAuthoringHttpStatus;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Tests\TestCase;

final class PropertyListingAuthoringHttpSecurityTest extends TestCase
{
    public const string ACCOUNT = '10000000-0000-4000-8000-000000000001';

    private const string PROPERTY = '20000000-0000-4000-8000-000000000001';

    private const string INTENT = '30000000-0000-4000-8000-000000000001';

    private CapturingPropertyListingAuthoringHttpRuntime $runtime;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        $this->withoutMiddleware(EncryptCookies::class);
        $this->app->instance(IdentityAccessHttpRuntime::class, new class implements IdentityAccessHttpRuntime
        {
            public function execute(IdentityAccessHttpCommand $command): IdentityAccessHttpResult
            {
                throw new \LogicException('Not used.');
            }

            public function inspectSession(string $secret, DateTimeImmutable $at): IdentityAccessSessionInspection
            {
                return $secret === 'valid-session'
                    ? IdentityAccessSessionInspection::valid(new AuthenticatedSessionContext(
                        AccountId::fromString(PropertyListingAuthoringHttpSecurityTest::ACCOUNT),
                        '91000000-0000-4000-8000-000000000002',
                    ))
                    : IdentityAccessSessionInspection::invalid();
            }
        });
        $this->runtime = new CapturingPropertyListingAuthoringHttpRuntime;
        $this->app->instance(PropertyListingAuthoringHttpRuntime::class, $this->runtime);
    }

    public function test_session_is_required_fail_closed(): void
    {
        $this->getJson('/api/authoring/properties/'.self::PROPERTY)
            ->assertUnauthorized()
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_account_scope_comes_only_from_certified_session(): void
    {
        $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'valid-session')
            ->getJson('/api/authoring/properties/'.self::PROPERTY)
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJson(['status' => 'succeeded', 'version' => 1]);

        self::assertSame(self::ACCOUNT, $this->runtime->last[1]);
        self::assertSame(self::PROPERTY, $this->runtime->last[2]);
    }

    public function test_mutations_require_uuid_idempotency_and_reject_unknown_fields(): void
    {
        $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'valid-session')
            ->postJson('/api/authoring/properties/'.self::PROPERTY, [])
            ->assertUnprocessable();

        $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'valid-session')
            ->withHeader('Idempotency-Key', self::INTENT)
            ->postJson('/api/authoring/properties/'.self::PROPERTY, ['accountId' => self::ACCOUNT])
            ->assertUnprocessable();
    }

    public function test_valid_mutation_forwards_only_validated_input_and_intent(): void
    {
        $property = [
            'propertyType' => 'apartment',
            'city' => 'Dakar',
            'neighborhood' => 'Almadies',
        ];
        $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'valid-session')
            ->withHeader('Idempotency-Key', self::INTENT)
            ->postJson('/api/authoring/properties/'.self::PROPERTY, $property)
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        self::assertSame(self::INTENT, $this->runtime->last[3]);
        self::assertSame($property, $this->runtime->last[4]);
    }

    public function test_property_surface_rejects_unknown_types_and_blank_locations(): void
    {
        $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'valid-session')
            ->withHeader('Idempotency-Key', self::INTENT)
            ->postJson('/api/authoring/properties/'.self::PROPERTY, [
                'propertyType' => 'castle',
                'city' => ' ',
                'neighborhood' => 'A',
            ])
            ->assertUnprocessable();
    }
}

final class CapturingPropertyListingAuthoringHttpRuntime implements PropertyListingAuthoringHttpRuntime
{
    /** @var array{PropertyListingAuthoringHttpOperation, string, ?string, ?string, array<string, mixed>} */
    public array $last;

    public function execute(PropertyListingAuthoringHttpOperation $operation, string $accountId, ?string $resourceId, ?string $intentId, array $input): PropertyListingAuthoringHttpResult
    {
        $this->last = [$operation, $accountId, $resourceId, $intentId, $input];

        return new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::Succeeded, ['version' => 1]);
    }
}
