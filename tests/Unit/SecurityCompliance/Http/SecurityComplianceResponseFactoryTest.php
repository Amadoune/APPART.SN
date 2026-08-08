<?php

namespace Tests\Unit\SecurityCompliance\Http;

use App\Http\SecurityCompliance\SecurityComplianceResponseFactory;
use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\IncidentResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\IncidentStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\PrivacyPolicyResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\PrivacyPolicyStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityAuditResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityAuditStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SecurityComplianceResponseFactoryTest extends TestCase
{
    #[DataProvider('mappings')]
    public function test_http_mapping_is_exhaustive(string $kind, object $result, int $expected): void
    {
        $response = (new SecurityComplianceResponseFactory)->{$kind}($result);
        self::assertSame($expected, $response->getStatusCode());
        self::assertSame(['status', 'observedAt'], array_keys($response->getData(true)));
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    /** @return iterable<string, array{string, object, int}> */
    public static function mappings(): iterable
    {
        $at = new SecurityComplianceObservedAt(new DateTimeImmutable('2026-08-04T12:00:00Z'));
        foreach ([['Available', 200], ['Missing', 404], ['Corrupted', 503], ['DependencyUnavailable', 503]] as [$case, $http]) {
            $secret = constant(SecretInventoryStatusV1::class.'::'.$case);
            yield 'secret '.$case => ['secretInventory', new SecretInventoryResultV1($secret, $at), $http];
            $audit = constant(SecurityAuditStatusV1::class.'::'.$case);
            yield 'audit '.$case => ['securityAudit', new SecurityAuditResultV1($audit, $at), $http];
            $incident = constant(IncidentStatusV1::class.'::'.$case);
            yield 'incident '.$case => ['incident', new IncidentResultV1($incident, $at), $http];
            $privacy = constant(PrivacyPolicyStatusV1::class.'::'.$case);
            yield 'privacy '.$case => ['privacyPolicy', new PrivacyPolicyResultV1($privacy, $at), $http];
            $control = constant(ComplianceControlStatusV1::class.'::'.$case);
            yield 'control '.$case => ['complianceControl', new ComplianceControlResultV1($control, $at), $http];
        }
    }
}
