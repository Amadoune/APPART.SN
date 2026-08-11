<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class IdentityAccessHttpRuntimeCompositionArchitectureTest extends TestCase
{
    public function test_runtime_composes_ports_without_sql_http_or_cryptography(): void
    {
        $runtime = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/IdentityAccessHttp/DeterministicIdentityAccessHttpRuntime.php');
        foreach (['PDO', 'DB::', 'SELECT ', 'INSERT ', 'UPDATE ', 'password_hash', 'password_verify', 'random_bytes', 'hash_hmac', 'Illuminate\\', 'Symfony\\'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $runtime);
        }
        foreach (['LoginIdentityResolverV1', 'CredentialVerifierV1', 'SessionSecretAuthorityV1', 'SessionPolicyEvaluatorV1', 'SessionReductionV1', 'IdentityAccessSessionStore', 'IdentityAccessOrchestrator'] as $dependency) {
            self::assertStringContainsString($dependency, $runtime);
        }
    }

    public function test_context_contains_only_account_and_session_id_and_is_not_serializable(): void
    {
        $context = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/IdentityAccessHttp/AuthenticatedSessionContext.php');
        self::assertStringContainsString('AccountId $accountId', $context);
        self::assertStringContainsString('string $sessionId', $context);
        self::assertStringContainsString('__serialize', $context);
        foreach (['secret', 'cookie', 'hmac', 'timestamp', 'policy', 'state'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, strtolower($context));
        }
    }
}
