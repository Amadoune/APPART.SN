<?php

namespace App\Http\AdministrationConsole;

use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueStatusV1;
use Illuminate\Http\JsonResponse;

final readonly class AdministrationConsoleResponseFactory
{
    public function operator(AdministrationOperatorResultV1 $result): JsonResponse
    {
        $status = match ($result->status) {
            AdministrationOperatorStatusV1::Available,
            AdministrationOperatorStatusV1::Unavailable => 200,
            AdministrationOperatorStatusV1::Missing => 404,
            AdministrationOperatorStatusV1::Corrupted,
            AdministrationOperatorStatusV1::DependencyUnavailable => 503,
        };

        return $this->response($result->status->value, $status);
    }

    public function queue(AdministrationQueueResultV1 $result): JsonResponse
    {
        $status = match ($result->status) {
            AdministrationQueueStatusV1::Ready,
            AdministrationQueueStatusV1::Empty => 200,
            AdministrationQueueStatusV1::Missing => 404,
            AdministrationQueueStatusV1::Corrupted,
            AdministrationQueueStatusV1::DependencyUnavailable => 503,
        };

        return $this->response($result->status->value, $status);
    }

    public function audit(AdministrationAuditResultV1 $result): JsonResponse
    {
        $status = match ($result->status) {
            AdministrationAuditStatusV1::Available => 200,
            AdministrationAuditStatusV1::Missing => 404,
            AdministrationAuditStatusV1::Corrupted,
            AdministrationAuditStatusV1::DependencyUnavailable => 503,
        };

        return $this->response($result->status->value, $status);
    }

    private function response(string $status, int $httpStatus): JsonResponse
    {
        return new JsonResponse(['status' => $status], $httpStatus, ['Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
