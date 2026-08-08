<?php

namespace App\Http\ExperienceAcceptance;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\AccessibilityComplianceResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\AccessibilityComplianceStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\EndToEndReadinessResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\EndToEndReadinessStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\PerformanceReadinessResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\PerformanceReadinessStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ReleaseCandidateResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ReleaseCandidateStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ResponsiveComplianceResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ResponsiveComplianceStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\UserAcceptanceResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\UserAcceptanceStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\UserExperienceResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\UserExperienceStatusV1;
use Illuminate\Http\JsonResponse;

final readonly class ExperienceAcceptanceResponseFactory
{
    public function responsiveCompliance(ResponsiveComplianceResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, $this->code($result->status));
    }

    public function accessibilityCompliance(AccessibilityComplianceResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, $this->code($result->status));
    }

    public function userExperience(UserExperienceResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, $this->code($result->status));
    }

    public function endToEndReadiness(EndToEndReadinessResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, $this->code($result->status));
    }

    public function performanceReadiness(PerformanceReadinessResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, $this->code($result->status));
    }

    public function userAcceptance(UserAcceptanceResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, $this->code($result->status));
    }

    public function releaseCandidate(ReleaseCandidateResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, $this->code($result->status));
    }

    private function code(ResponsiveComplianceStatusV1|AccessibilityComplianceStatusV1|UserExperienceStatusV1|EndToEndReadinessStatusV1|PerformanceReadinessStatusV1|UserAcceptanceStatusV1|ReleaseCandidateStatusV1 $status): int
    {
        return match ($status->value) {
            'available' => 200, 'missing' => 404, 'corrupted', 'dependency_unavailable' => 503
        };
    }

    private function response(string $status, string $observedAt, int $code): JsonResponse
    {
        return new JsonResponse(['status' => $status, 'observedAt' => $observedAt], $code, ['Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
