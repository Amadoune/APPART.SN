<?php

namespace App\Http\Controllers;

use App\Http\Notifications\NotificationsHttpRuntimeV1;
use App\Http\Notifications\NotificationsResponseFactory;
use App\Http\Requests\NotificationPreferenceRequest;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationPreferenceReaderV1;
use Illuminate\Http\JsonResponse;

final class NotificationPreferenceController extends Controller implements NotificationsHttpRuntimeV1
{
    public function __construct(private readonly NotificationPreferenceReaderV1 $reader, private readonly NotificationsResponseFactory $responses) {}

    public function __invoke(NotificationPreferenceRequest $request): JsonResponse
    {
        return $this->responses->preference($this->reader->read($request->subjectKey(), $request->observedAt()));
    }
}
