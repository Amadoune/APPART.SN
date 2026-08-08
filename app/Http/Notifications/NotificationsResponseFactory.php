<?php

namespace App\Http\Notifications;

use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateStatusV1;
use Illuminate\Http\JsonResponse;

final readonly class NotificationsResponseFactory
{
    public function preference(NotificationPreferenceResultV1 $result): JsonResponse
    {
        $status = match ($result->status) {
            NotificationPreferenceStatusV1::Enabled,
            NotificationPreferenceStatusV1::Disabled => 200,
            NotificationPreferenceStatusV1::Missing => 404,
            NotificationPreferenceStatusV1::Corrupted,
            NotificationPreferenceStatusV1::DependencyUnavailable => 503,
        };

        return $this->response($result->status->value, $status);
    }

    public function template(NotificationTemplateResultV1 $result): JsonResponse
    {
        $status = match ($result->status) {
            NotificationTemplateStatusV1::Available => 200,
            NotificationTemplateStatusV1::Missing => 404,
            NotificationTemplateStatusV1::Corrupted,
            NotificationTemplateStatusV1::DependencyUnavailable => 503,
        };

        return $this->response($result->status->value, $status);
    }

    public function channel(NotificationChannelResultV1 $result): JsonResponse
    {
        $status = match ($result->status) {
            NotificationChannelStatusV1::Allowed,
            NotificationChannelStatusV1::Blocked => 200,
            NotificationChannelStatusV1::Missing => 404,
            NotificationChannelStatusV1::Corrupted,
            NotificationChannelStatusV1::DependencyUnavailable => 503,
        };

        return $this->response($result->status->value, $status);
    }

    private function response(string $status, int $httpStatus): JsonResponse
    {
        return new JsonResponse(['status' => $status], $httpStatus, ['Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
