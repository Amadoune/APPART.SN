<?php

namespace Tests\Feature;

use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\ComplianceControlReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\IncidentReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\PrivacyPolicyReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\SecretInventoryReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\SecurityAuditReaderV1;
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
use Tests\TestCase;

final class SecurityComplianceHttpFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(SecretInventoryReaderV1::class, new class implements SecretInventoryReaderV1
        {
            public function read(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): SecretInventoryResultV1
            {
                return new SecretInventoryResultV1(SecretInventoryStatusV1::Available, $observedAt);
            }
        });
        $this->app->instance(SecurityAuditReaderV1::class, new class implements SecurityAuditReaderV1
        {
            public function read(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): SecurityAuditResultV1
            {
                return new SecurityAuditResultV1(SecurityAuditStatusV1::Available, $observedAt);
            }
        });
        $this->app->instance(IncidentReaderV1::class, new class implements IncidentReaderV1
        {
            public function read(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): IncidentResultV1
            {
                return new IncidentResultV1(IncidentStatusV1::Available, $observedAt);
            }
        });
        $this->app->instance(PrivacyPolicyReaderV1::class, new class implements PrivacyPolicyReaderV1
        {
            public function read(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): PrivacyPolicyResultV1
            {
                return new PrivacyPolicyResultV1(PrivacyPolicyStatusV1::Available, $observedAt);
            }
        });
        $this->app->instance(ComplianceControlReaderV1::class, new class implements ComplianceControlReaderV1
        {
            public function read(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): ComplianceControlResultV1
            {
                return new ComplianceControlResultV1(ComplianceControlStatusV1::Available, $observedAt);
            }
        });
    }

    public function test_five_public_endpoints_consume_only_public_readers(): void
    {
        $query = '?subjectKey=security-compliance%3A42&observedAt=2026-08-04T10%3A00%3A00.123456%2B00%3A00';
        foreach (['secret-inventory', 'security-audit', 'incident', 'privacy-policy', 'compliance-control'] as $path) {
            $this->getJson('/api/security-compliance/'.$path.$query)->assertOk()->assertExactJson(['status' => 'available', 'observedAt' => '2026-08-04T10:00:00.123456Z'])->assertHeader('Cache-Control', 'no-store, private')->assertHeader('X-Content-Type-Options', 'nosniff');
        }
    }

    public function test_requests_reject_missing_and_unknown_inputs(): void
    {
        $this->getJson('/api/security-compliance/secret-inventory')->assertUnprocessable();
        $this->getJson('/api/security-compliance/compliance-control?subjectKey=x&observedAt=2026-08-04T10%3A00%3A00.123456%2B00%3A00&secret=value')->assertUnprocessable();
    }
}
