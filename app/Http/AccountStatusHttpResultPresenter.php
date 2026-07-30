<?php

namespace App\Http;

use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\AccountStatusOrchestrationResult;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\AccountStatusOrchestrationStatus;
use Illuminate\Http\JsonResponse;

final readonly class AccountStatusHttpResultPresenter
{
    public function response(AccountStatusOrchestrationResult $result): JsonResponse
    {
        return match ($result->status) {
            AccountStatusOrchestrationStatus::Applied => $this->json(
                'account_status.applied',
                $result,
                200,
            ),
            AccountStatusOrchestrationStatus::AlreadyInState => $this->json(
                'account_status.already_in_state',
                $result,
                200,
            ),
            AccountStatusOrchestrationStatus::AccountMissing => $this->json(
                'account_status.not_found',
                $result,
                404,
            ),
            AccountStatusOrchestrationStatus::VersionConflict => $this->json(
                'account_status.version_conflict',
                $result,
                409,
            ),
            AccountStatusOrchestrationStatus::InvalidContext => $this->json(
                'account_status.invalid_context',
                $result,
                422,
            ),
            AccountStatusOrchestrationStatus::PersistenceRejected => $this->json(
                'account_status.persistence_rejected',
                $result,
                409,
            ),
            AccountStatusOrchestrationStatus::PersistenceCorrupted => $this->json(
                'account_status.persistence_unavailable',
                $result,
                503,
            ),
            AccountStatusOrchestrationStatus::InspectionCorrupted => $this->json(
                'account_status.inspection_unavailable',
                $result,
                503,
            ),
        };
    }

    public function unavailable(): JsonResponse
    {
        return response()->json([
            'code' => 'account_status.runtime_unavailable',
        ], 503);
    }

    private function json(
        string $code,
        AccountStatusOrchestrationResult $result,
        int $httpStatus,
    ): JsonResponse {
        return response()->json(array_filter([
            'code' => $code,
            'status' => $result->status->value,
            'state' => $result->state?->value,
        ], static fn (mixed $value): bool => $value !== null), $httpStatus);
    }
}
