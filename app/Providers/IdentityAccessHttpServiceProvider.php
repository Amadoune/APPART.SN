<?php

namespace App\Providers;

use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\Contract\IdentityAccessSessionStore;
use App\Application\IdentityAccessHttp\DeterministicIdentityAccessHttpRuntime;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Argon2IdCredentialHashAuthority;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Contract\CredentialSourceV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Contract\LoginIdentitySourceV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\CredentialHashAuthorityV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\CredentialVerifierV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\DeterministicCredentialVerifier;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\DeterministicLoginIdentityResolver;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\DeterministicSessionPolicyEvaluator;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\DeterministicSessionReduction;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\HmacSha256SessionSecretAuthority;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\LoginIdentityResolverV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\SessionPolicyEvaluatorV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\SessionReductionV1;
use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\SessionSecretAuthorityV1;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\IdentityAccessCompletionPersistenceMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAuthenticationAuthoritySource;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlSessionStore;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class IdentityAccessHttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlAuthenticationAuthoritySource::class);
        $this->app->alias(PostgreSqlAuthenticationAuthoritySource::class, LoginIdentitySourceV1::class);
        $this->app->alias(PostgreSqlAuthenticationAuthoritySource::class, CredentialSourceV1::class);
        $this->app->singleton(DeterministicLoginIdentityResolver::class);
        $this->app->alias(DeterministicLoginIdentityResolver::class, LoginIdentityResolverV1::class);
        $this->app->singleton(Argon2IdCredentialHashAuthority::class);
        $this->app->alias(Argon2IdCredentialHashAuthority::class, CredentialHashAuthorityV1::class);
        $this->app->singleton(DeterministicCredentialVerifier::class);
        $this->app->alias(DeterministicCredentialVerifier::class, CredentialVerifierV1::class);
        $this->app->singleton(HmacSha256SessionSecretAuthority::class, static fn (Application $app): HmacSha256SessionSecretAuthority => new HmacSha256SessionSecretAuthority(
            (string) $app['config']->get('identity_access_http.session_hmac_key_id'),
            (string) $app['config']->get('identity_access_http.session_hmac_key'),
        ));
        $this->app->alias(HmacSha256SessionSecretAuthority::class, SessionSecretAuthorityV1::class);
        $this->app->singleton(DeterministicSessionPolicyEvaluator::class);
        $this->app->alias(DeterministicSessionPolicyEvaluator::class, SessionPolicyEvaluatorV1::class);
        $this->app->singleton(DeterministicSessionReduction::class);
        $this->app->alias(DeterministicSessionReduction::class, SessionReductionV1::class);
        $this->app->singleton(IdentityAccessCompletionPersistenceMapper::class);
        $this->app->singleton(PostgreSqlSessionStore::class);
        $this->app->alias(PostgreSqlSessionStore::class, IdentityAccessSessionStore::class);
        $this->app->singleton(DeterministicIdentityAccessHttpRuntime::class);
        $this->app->alias(DeterministicIdentityAccessHttpRuntime::class, IdentityAccessHttpRuntime::class);
    }

    public function boot(): void
    {
        RateLimiter::for('iam-login', fn (Request $request): Limit => Limit::perMinute(
            (int) config('identity_access_http.rate_limit.login_per_minute'),
        )->by($this->riskKey($request, 'identifier')));

        RateLimiter::for('iam-recovery', fn (Request $request): Limit => Limit::perMinute(
            (int) config('identity_access_http.rate_limit.recovery_per_minute'),
        )->by($this->riskKey($request, 'identifier')));

        RateLimiter::for('iam-authenticated', fn (Request $request): Limit => Limit::perMinute(
            (int) config('identity_access_http.rate_limit.authenticated_per_minute'),
        )->by(hash('sha256', (string) $request->cookie(
            (string) config('identity_access_http.cookie.name'),
            $request->ip(),
        ))));
    }

    private function riskKey(Request $request, string $field): string
    {
        return hash_hmac(
            'sha256',
            mb_strtolower(trim((string) $request->input($field))).'|'.$request->ip(),
            (string) config('identity_access_http.risk_hmac_key'),
        );
    }
}
