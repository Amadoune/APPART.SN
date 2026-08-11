<?php

namespace Tests\Feature;

use App\Application\IdentityAccessHttp\AuthenticatedSessionContext;
use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\IdentityAccessHttpCommand;
use App\Application\IdentityAccessHttp\IdentityAccessHttpResult;
use App\Application\IdentityAccessHttp\IdentityAccessHttpStatus;
use App\Application\IdentityAccessHttp\IdentityAccessSessionInspection;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use Illuminate\Cookie\Middleware\EncryptCookies;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class IdentityAccessHttpSecurityTest extends TestCase
{
    private FakeIdentityAccessHttpRuntime $runtime;

    protected function setUp(): void
    {
        parent::setUp();
        $this->runtime = new FakeIdentityAccessHttpRuntime;
        $this->app->instance(IdentityAccessHttpRuntime::class, $this->runtime);
        config()->set('app.key', 'base64:'.base64_encode(str_repeat('k', 32)));
        config()->set('identity_access_http.rate_limit.login_per_minute', 5);
        config()->set('identity_access_http.rate_limit.recovery_per_minute', 3);
    }

    #[Test]
    public function login_sets_only_a_secure_http_only_strict_cookie(): void
    {
        $this->runtime->next = new IdentityAccessHttpResult(
            IdentityAccessHttpStatus::Succeeded,
            [],
            'ephemeral-session-secret',
            900,
        );

        $response = $this->postJson('/api/identity-access/login', [
            'identifier' => 'user@example.test',
            'credential' => 'correct horse battery staple',
            'requestedAt' => '2026-07-27T10:00:00.000000Z',
        ], ['Idempotency-Key' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa']);

        $response->assertOk()->assertExactJson(['status' => 'succeeded']);
        self::assertStringNotContainsString('ephemeral-session-secret', $response->getContent());
        $cookie = $response->headers->getCookies()[0] ?? null;
        self::assertNotNull($cookie);
        self::assertSame('__Host-appart_session', $cookie->getName());
        self::assertTrue($cookie->isSecure());
        self::assertTrue($cookie->isHttpOnly());
        self::assertSame('strict', $cookie->getSameSite());
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    #[Test]
    public function login_failures_are_publicly_indistinguishable(): void
    {
        $this->runtime->next = new IdentityAccessHttpResult(IdentityAccessHttpStatus::GenericFailure);
        $response = $this->postJson('/api/identity-access/login', $this->loginInput(), [
            'Idempotency-Key' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        ]);

        $response->assertStatus(401)->assertExactJson(['status' => 'authentication_failed']);
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    #[Test]
    public function recovery_never_discloses_account_existence(): void
    {
        $input = [
            'identifier' => 'possibly-missing@example.test',
            'requestedAt' => '2026-07-27T10:00:00.000000Z',
        ];
        $headers = ['Idempotency-Key' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'];
        $this->runtime->next = new IdentityAccessHttpResult(IdentityAccessHttpStatus::GenericFailure);
        $missing = $this->postJson('/api/identity-access/password-recovery', $input, $headers);
        $this->runtime->next = new IdentityAccessHttpResult(IdentityAccessHttpStatus::Accepted);
        $existing = $this->postJson('/api/identity-access/password-recovery', $input, [
            'Idempotency-Key' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
        ]);

        $missing->assertStatus(202)->assertExactJson(['status' => 'accepted']);
        $existing->assertStatus(202)->assertExactJson(['status' => 'accepted']);
        self::assertSame($missing->getContent(), $existing->getContent());
    }

    #[Test]
    public function protected_endpoints_require_a_valid_session_and_are_self_scoped(): void
    {
        $this->withoutMiddleware(EncryptCookies::class);
        $this->getJson('/api/identity-access/profile')
            ->assertStatus(401)
            ->assertExactJson(['status' => 'authentication_required']);

        $this->runtime->next = new IdentityAccessHttpResult(
            IdentityAccessHttpStatus::Succeeded,
            ['displayName' => 'Profile'],
        );
        $protected = $this->withCredentials()
            ->withUnencryptedCookie('__Host-appart_session', 'valid-secret')
            ->getJson('/api/identity-access/profile');
        self::assertSame('valid-secret', $this->runtime->lastSecret);
        $protected->assertOk()->assertJsonPath('displayName', 'Profile');

        self::assertNotNull($this->runtime->last);
        self::assertSame('dddddddd-dddd-4ddd-8ddd-dddddddddddd', $this->runtime->last->authenticatedAccount?->value);
        self::assertSame('eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee', $this->runtime->last->authenticatedSession?->sessionId);
        self::assertArrayNotHasKey('accountId', $this->runtime->last->input);
    }

    #[Test]
    public function unknown_fields_and_missing_idempotency_are_rejected(): void
    {
        $this->postJson('/api/identity-access/login', $this->loginInput() + ['admin' => true])
            ->assertStatus(422);
    }

    #[Test]
    public function login_rate_limit_is_enforced_without_echoing_the_identifier(): void
    {
        $this->runtime->next = new IdentityAccessHttpResult(IdentityAccessHttpStatus::GenericFailure);
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/identity-access/login', $this->loginInput(), [
                'Idempotency-Key' => sprintf('aaaaaaaa-aaaa-4aaa-8aaa-%012d', $attempt),
            ])->assertStatus(401);
        }
        $limited = $this->postJson('/api/identity-access/login', $this->loginInput(), [
            'Idempotency-Key' => 'aaaaaaaa-aaaa-4aaa-8aaa-000000000006',
        ]);

        $limited->assertStatus(429);
        self::assertStringNotContainsString('user@example.test', $limited->getContent());
    }

    /** @return array{identifier:string,credential:string,requestedAt:string} */
    private function loginInput(): array
    {
        return [
            'identifier' => 'user@example.test',
            'credential' => 'wrong-password',
            'requestedAt' => '2026-07-27T10:00:00.000000Z',
        ];
    }
}

final class FakeIdentityAccessHttpRuntime implements IdentityAccessHttpRuntime
{
    public IdentityAccessHttpResult $next;

    public ?IdentityAccessHttpCommand $last = null;

    public ?string $lastSecret = null;

    public function __construct()
    {
        $this->next = new IdentityAccessHttpResult(IdentityAccessHttpStatus::Unavailable);
    }

    public function execute(IdentityAccessHttpCommand $command): IdentityAccessHttpResult
    {
        $this->last = $command;

        return $this->next;
    }

    public function inspectSession(string $secret, DateTimeImmutable $at): IdentityAccessSessionInspection
    {
        $this->lastSecret = $secret;

        return $secret === 'valid-secret'
            ? IdentityAccessSessionInspection::valid(
                new AuthenticatedSessionContext(
                    AccountId::fromString('dddddddd-dddd-4ddd-8ddd-dddddddddddd'),
                    'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
                ),
            )
            : IdentityAccessSessionInspection::invalid();
    }
}
