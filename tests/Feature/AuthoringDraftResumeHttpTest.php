<?php

namespace Tests\Feature;

use App\Application\AuthoringDraftResume\AuthoringDraftResumeResult;
use App\Application\AuthoringDraftResume\AuthoringDraftResumeStatus;
use App\Application\AuthoringDraftResume\Contract\AuthoringDraftResumeReaderV1;
use App\Application\IdentityAccessHttp\AuthenticatedSessionContext;
use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\IdentityAccessHttpCommand;
use App\Application\IdentityAccessHttp\IdentityAccessHttpResult;
use App\Application\IdentityAccessHttp\IdentityAccessSessionInspection;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Tests\TestCase;

final class AuthoringDraftResumeHttpTest extends TestCase
{
    public const string ACCOUNT = '82000000-0000-4000-8000-000000000001';

    private const string LISTING = '82000000-0000-4000-8000-000000000002';

    private FakeAuthoringDraftResumeReader $reader;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('r', 32))]);
        $this->withoutMiddleware(EncryptCookies::class);
        $this->app->instance(IdentityAccessHttpRuntime::class, new ResumeIdentityRuntime);
        $this->reader = new FakeAuthoringDraftResumeReader;
        $this->app->instance(AuthoringDraftResumeReaderV1::class, $this->reader);
    }

    public function test_session_is_required(): void
    {
        $this->get('/authoring/workspace/'.self::LISTING)->assertUnauthorized();
    }

    public function test_owner_resume_returns_bootstrap_with_preserved_ids_and_versions(): void
    {
        $this->reader->result = new AuthoringDraftResumeResult(AuthoringDraftResumeStatus::Available, [
            'mode' => 'resume', 'step' => 6, 'propertyId' => '82000000-0000-4000-8000-000000000003',
            'listingId' => self::LISTING, 'expectedAuthoringVersion' => 1, 'expectedVersion' => 1,
            'property' => [], 'draft' => [], 'geography' => [], 'media' => ['items' => []],
        ]);

        $this->authenticated()->get('/authoring/workspace/'.self::LISTING)
            ->assertOk()
            ->assertSee('authoring-resume-bootstrap', false)
            ->assertSee(self::LISTING, false)
            ->assertSee('"expectedVersion":1', false);

        self::assertSame([self::ACCOUNT, self::LISTING], $this->reader->last);
    }

    public function test_invalid_listing_id_is_422_and_never_calls_reader(): void
    {
        $this->authenticated()->get('/authoring/workspace/not-a-uuid')->assertUnprocessable();
        self::assertNull($this->reader->last);
    }

    public function test_cross_owner_or_missing_is_indistinguishable(): void
    {
        $this->reader->result = new AuthoringDraftResumeResult(AuthoringDraftResumeStatus::NotFoundOrForbidden);

        $this->authenticated()->get('/authoring/workspace/'.self::LISTING)->assertNotFound();
    }

    public function test_closed_failures_keep_the_blueprint_http_mapping(): void
    {
        foreach ([
            AuthoringDraftResumeStatus::Incomplete->value => 409,
            AuthoringDraftResumeStatus::StateConflict->value => 409,
            AuthoringDraftResumeStatus::Corrupted->value => 500,
            AuthoringDraftResumeStatus::DependencyUnavailable->value => 503,
        ] as $status => $http) {
            $this->reader->result = new AuthoringDraftResumeResult(AuthoringDraftResumeStatus::from($status));
            $this->authenticated()->get('/authoring/workspace/'.self::LISTING)->assertStatus($http);
        }
    }

    private function authenticated(): self
    {
        return $this->withCredentials()->withUnencryptedCookie('__Host-appart_session', 'resume-session');
    }
}

final class FakeAuthoringDraftResumeReader implements AuthoringDraftResumeReaderV1
{
    public AuthoringDraftResumeResult $result;

    /** @var array{string, string}|null */
    public ?array $last = null;

    public function __construct()
    {
        $this->result = new AuthoringDraftResumeResult(AuthoringDraftResumeStatus::DependencyUnavailable);
    }

    public function read(string $accountId, string $listingId): AuthoringDraftResumeResult
    {
        $this->last = [$accountId, $listingId];

        return $this->result;
    }
}

final class ResumeIdentityRuntime implements IdentityAccessHttpRuntime
{
    public function execute(IdentityAccessHttpCommand $command): IdentityAccessHttpResult
    {
        throw new \LogicException('Not used.');
    }

    public function inspectSession(string $secret, DateTimeImmutable $at): IdentityAccessSessionInspection
    {
        return $secret === 'resume-session'
            ? IdentityAccessSessionInspection::valid(new AuthenticatedSessionContext(
                AccountId::fromString(AuthoringDraftResumeHttpTest::ACCOUNT),
                '82000000-0000-4000-8000-000000000009',
            ))
            : IdentityAccessSessionInspection::invalid();
    }
}
