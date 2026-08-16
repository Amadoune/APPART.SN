<?php

namespace Tests\Feature;

use App\Application\IdentityAccessHttp\AuthenticatedSessionContext;
use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\IdentityAccessHttpCommand;
use App\Application\IdentityAccessHttp\IdentityAccessHttpResult;
use App\Application\IdentityAccessHttp\IdentityAccessSessionInspection;
use App\Application\PropertyAuthoringGeographySelection\Contract\GeographySelectionReplayValidatorV1;
use App\Application\PropertyAuthoringGeographySelection\DeterministicGeographySelectionReplayValidatorV1;
use Appart\Modules\Geography\Application\GeographySelection\Contract\GeographySelectionReaderV1;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionItem;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionQuery;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionResult;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionStatus;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class PropertyAuthoringGeographySelectionHttpTest extends TestCase
{
    public const string ACCOUNT = '52000000-0000-4000-8000-000000000001';

    private const string PARENT = '52000000-0000-4000-8000-000000000002';

    private MutableSelectionReader $reader;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('g', 32))]);
        $this->withoutMiddleware(EncryptCookies::class);
        $this->app->instance(IdentityAccessHttpRuntime::class, new GeographySelectionIdentityRuntime);
        $this->reader = new MutableSelectionReader;
        $this->app->instance(GeographySelectionReaderV1::class, $this->reader);
    }

    public function test_route_requires_a_real_session(): void
    {
        $this->getJson('/api/authoring/geography/selections?type=country')->assertUnauthorized();
    }

    public function test_server_replay_validator_has_a_real_binding(): void
    {
        self::assertInstanceOf(DeterministicGeographySelectionReplayValidatorV1::class, $this->app->make(GeographySelectionReplayValidatorV1::class));
    }

    public function test_available_and_empty_are_minimal_no_store_responses(): void
    {
        $this->reader->result = new GeographySelectionResult(GeographySelectionStatus::Available, [
            new GeographySelectionItem('52000000-0000-4000-8000-000000000003', 'Dakar', 'city', self::PARENT),
        ], 'opaque-next');
        $this->authenticated('/api/authoring/geography/selections?type=city&parentPlaceId='.self::PARENT)
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertExactJson(['items' => [[
                'placeId' => '52000000-0000-4000-8000-000000000003', 'label' => 'Dakar', 'type' => 'city', 'parentPlaceId' => self::PARENT,
            ]], 'nextCursor' => 'opaque-next']);

        $this->reader->result = new GeographySelectionResult(GeographySelectionStatus::Empty);
        $this->authenticated('/api/authoring/geography/selections?type=country')->assertOk()->assertExactJson(['items' => [], 'nextCursor' => null]);
    }

    /** @return iterable<string, array{GeographySelectionStatus, int}> */
    public static function closedStatuses(): iterable
    {
        yield 'missing' => [GeographySelectionStatus::Missing, 404];
        yield 'corrupted' => [GeographySelectionStatus::Corrupted, 500];
        yield 'dependency unavailable' => [GeographySelectionStatus::DependencyUnavailable, 503];
    }

    #[DataProvider('closedStatuses')]
    public function test_closed_f1_statuses_have_exact_http_mapping(GeographySelectionStatus $status, int $http): void
    {
        $this->reader->result = new GeographySelectionResult($status);
        $this->authenticated('/api/authoring/geography/selections?type=country')->assertStatus($http);
    }

    public function test_invalid_unknown_and_free_text_parameters_are_rejected(): void
    {
        $this->authenticated('/api/authoring/geography/selections?type=city')->assertUnprocessable();
        $this->authenticated('/api/authoring/geography/selections?type=country&ownerAccountId='.self::ACCOUNT)->assertUnprocessable();
        $this->authenticated('/api/authoring/geography/selections?type=country&city=Dakar')->assertUnprocessable();
        $this->authenticated('/api/authoring/geography/selections?type=country&limit=101')->assertUnprocessable();
    }

    public function test_workspace_exposes_hierarchical_selection_and_replay_context_without_authoring_persistence(): void
    {
        $this->authenticated('/authoring/workspace')->assertOk()
            ->assertSee('data-geography-selection', false)
            ->assertSee('name="geographicPlaceId"', false)
            ->assertSee('name="geographicSelectionCursor"', false)
            ->assertSee('name="geographicSelectionLimit"', false);
    }

    private function authenticated(string $uri): TestResponse
    {
        return $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'geography-session')->getJson($uri);
    }
}

final class MutableSelectionReader implements GeographySelectionReaderV1
{
    public GeographySelectionResult $result;

    public function __construct()
    {
        $this->result = new GeographySelectionResult(GeographySelectionStatus::Empty);
    }

    public function read(GeographySelectionQuery $query): GeographySelectionResult
    {
        return $this->result;
    }
}

final class GeographySelectionIdentityRuntime implements IdentityAccessHttpRuntime
{
    public function execute(IdentityAccessHttpCommand $command): IdentityAccessHttpResult
    {
        throw new \LogicException('Not used.');
    }

    public function inspectSession(string $secret, DateTimeImmutable $at): IdentityAccessSessionInspection
    {
        return $secret === 'geography-session'
            ? IdentityAccessSessionInspection::valid(new AuthenticatedSessionContext(AccountId::fromString(PropertyAuthoringGeographySelectionHttpTest::ACCOUNT), '52000000-0000-4000-8000-000000000009'))
            : IdentityAccessSessionInspection::invalid();
    }
}
