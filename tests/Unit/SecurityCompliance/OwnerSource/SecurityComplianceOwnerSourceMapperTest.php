<?php

namespace Tests\Unit\SecurityCompliance\OwnerSource;

use Appart\Modules\SecurityCompliance\Application\OwnerSource\ComplianceControlRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\IncidentRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\PrivacyPolicyRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecretInventoryRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityAuditRevisionState;
use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\IncidentStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\PrivacyPolicyStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityAuditStatusV1;
use Appart\Modules\SecurityCompliance\Infrastructure\Persistence\SecurityComplianceOwnerSourceMapper;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class SecurityComplianceOwnerSourceMapperTest extends TestCase
{
    #[DataProvider('states')]
    public function test_round_trip_is_canonical(string $toRow, string $toState, object $state): void
    {
        $mapper = new SecurityComplianceOwnerSourceMapper;
        $row = $mapper->{$toRow}($state);
        $restored = $mapper->{$toState}($row);
        self::assertSame($state->subjectKey, $restored->subjectKey);
        self::assertSame($state->revision, $restored->revision);
        self::assertSame($state->decision, $restored->decision);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $row['revision_checksum']);
        self::assertSame('2026-08-04T10:00:00.123456Z', $row['effective_at']);
    }

    public function test_checksum_corruption_is_rejected(): void
    {
        $mapper = new SecurityComplianceOwnerSourceMapper;
        $row = $mapper->secretInventoryToRow(new SecretInventoryRevisionState('scope:1', 1, SecretInventoryStatusV1::Available, self::effective(), self::recorded()));
        $row['revision_checksum'] = str_repeat('0', 64);
        $this->expectException(RuntimeException::class);
        $mapper->toSecretInventoryState($row);
    }

    /** @return iterable<string, array{string,string,object}> */
    public static function states(): iterable
    {
        yield 'secret inventory' => ['secretInventoryToRow', 'toSecretInventoryState', new SecretInventoryRevisionState('scope:1', 1, SecretInventoryStatusV1::Available, self::effective(), self::recorded())];
        yield 'security audit' => ['securityAuditToRow', 'toSecurityAuditState', new SecurityAuditRevisionState('scope:1', 1, SecurityAuditStatusV1::Available, self::effective(), self::recorded())];
        yield 'incident' => ['incidentToRow', 'toIncidentState', new IncidentRevisionState('scope:1', 1, IncidentStatusV1::Available, self::effective(), self::recorded())];
        yield 'privacy policy' => ['privacyPolicyToRow', 'toPrivacyPolicyState', new PrivacyPolicyRevisionState('scope:1', 1, PrivacyPolicyStatusV1::Available, self::effective(), self::recorded())];
        yield 'compliance control' => ['complianceControlToRow', 'toComplianceControlState', new ComplianceControlRevisionState('scope:1', 1, ComplianceControlStatusV1::Available, self::effective(), self::recorded())];
    }

    private static function effective(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-04T12:00:00.123456+02:00');
    }

    private static function recorded(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-04T12:00:01.123456+02:00');
    }
}
