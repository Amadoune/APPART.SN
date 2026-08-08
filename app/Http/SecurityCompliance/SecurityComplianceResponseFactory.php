<?php

namespace App\Http\SecurityCompliance;

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
use Illuminate\Http\JsonResponse;

final readonly class SecurityComplianceResponseFactory
{
    public function secretInventory(SecretInventoryResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, $this->secretInventoryStatus($result->status));
    }

    public function securityAudit(SecurityAuditResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, $this->securityAuditStatus($result->status));
    }

    public function incident(IncidentResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, $this->incidentStatus($result->status));
    }

    public function privacyPolicy(PrivacyPolicyResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, $this->privacyPolicyStatus($result->status));
    }

    public function complianceControl(ComplianceControlResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, $this->complianceControlStatus($result->status));
    }

    private function secretInventoryStatus(SecretInventoryStatusV1 $status): int
    {
        return match ($status) {
            SecretInventoryStatusV1::Available => 200, SecretInventoryStatusV1::Missing => 404, SecretInventoryStatusV1::Corrupted, SecretInventoryStatusV1::DependencyUnavailable => 503
        };
    }

    private function securityAuditStatus(SecurityAuditStatusV1 $status): int
    {
        return match ($status) {
            SecurityAuditStatusV1::Available => 200, SecurityAuditStatusV1::Missing => 404, SecurityAuditStatusV1::Corrupted, SecurityAuditStatusV1::DependencyUnavailable => 503
        };
    }

    private function incidentStatus(IncidentStatusV1 $status): int
    {
        return match ($status) {
            IncidentStatusV1::Available => 200, IncidentStatusV1::Missing => 404, IncidentStatusV1::Corrupted, IncidentStatusV1::DependencyUnavailable => 503
        };
    }

    private function privacyPolicyStatus(PrivacyPolicyStatusV1 $status): int
    {
        return match ($status) {
            PrivacyPolicyStatusV1::Available => 200, PrivacyPolicyStatusV1::Missing => 404, PrivacyPolicyStatusV1::Corrupted, PrivacyPolicyStatusV1::DependencyUnavailable => 503
        };
    }

    private function complianceControlStatus(ComplianceControlStatusV1 $status): int
    {
        return match ($status) {
            ComplianceControlStatusV1::Available => 200, ComplianceControlStatusV1::Missing => 404, ComplianceControlStatusV1::Corrupted, ComplianceControlStatusV1::DependencyUnavailable => 503
        };
    }

    private function response(string $status, string $observedAt, int $httpStatus): JsonResponse
    {
        return new JsonResponse(['status' => $status, 'observedAt' => $observedAt], $httpStatus, ['Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
