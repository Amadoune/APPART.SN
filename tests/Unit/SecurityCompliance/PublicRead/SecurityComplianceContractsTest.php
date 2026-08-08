<?php

namespace Tests\Unit\SecurityCompliance\PublicRead;

use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\CryptographyPolicyResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\CryptographyPolicyStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\DataExportResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\DataExportStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\DataRetentionResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\DataRetentionStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\IncidentResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\IncidentStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\PrivacyPolicyResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\PrivacyPolicyStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityAuditResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityAuditStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SecurityComplianceContractsTest extends TestCase
{
    #[DataProvider('results')]
    public function test_results_expose_only_status_and_canonical_observed_at(object $result, string $status): void
    {
        self::assertSame($status, $result->status->value);
        self::assertSame('2026-08-04T10:00:00.123456Z', $result->observedAt);
        $properties = array_keys(get_object_vars($result));
        sort($properties);
        self::assertSame(['observedAt', 'status'], $properties);
    }

    /** @return iterable<string, array{object, string}> */
    public static function results(): iterable
    {
        $at = self::observedAt();
        foreach (SecretInventoryStatusV1::cases() as $status) {
            yield 'secret '.$status->value => [new SecretInventoryResultV1($status, $at), $status->value];
        }
        foreach (CryptographyPolicyStatusV1::cases() as $status) {
            yield 'crypto '.$status->value => [new CryptographyPolicyResultV1($status, $at), $status->value];
        }
        foreach (SecurityAuditStatusV1::cases() as $status) {
            yield 'audit '.$status->value => [new SecurityAuditResultV1($status, $at), $status->value];
        }
        foreach (IncidentStatusV1::cases() as $status) {
            yield 'incident '.$status->value => [new IncidentResultV1($status, $at), $status->value];
        }
        foreach (PrivacyPolicyStatusV1::cases() as $status) {
            yield 'privacy '.$status->value => [new PrivacyPolicyResultV1($status, $at), $status->value];
        }
        foreach (DataRetentionStatusV1::cases() as $status) {
            yield 'retention '.$status->value => [new DataRetentionResultV1($status, $at), $status->value];
        }
        foreach (DataExportStatusV1::cases() as $status) {
            yield 'export '.$status->value => [new DataExportResultV1($status, $at), $status->value];
        }
        foreach (ComplianceControlStatusV1::cases() as $status) {
            yield 'control '.$status->value => [new ComplianceControlResultV1($status, $at), $status->value];
        }
    }

    public function test_subject_key_is_opaque_and_canonical(): void
    {
        self::assertSame('security-compliance:scope:1', (new SecurityComplianceSubjectKey('security-compliance:scope:1'))->canonical());
    }

    #[DataProvider('invalidSubjectKeys')]
    public function test_subject_key_rejects_non_canonical_values(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SecurityComplianceSubjectKey($value);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidSubjectKeys(): iterable
    {
        yield 'empty' => [''];
        yield 'leading space' => [' invalid'];
        yield 'trailing space' => ['invalid '];
        yield 'too long' => [str_repeat('x', 256)];
    }

    private static function observedAt(): SecurityComplianceObservedAt
    {
        return new SecurityComplianceObservedAt(new DateTimeImmutable('2026-08-04T12:00:00.123456+02:00'));
    }
}
