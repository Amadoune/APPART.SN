<?php

namespace App\Http\Controllers;

use App\Http\Notifications\NotificationsHttpRuntimeV1;
use App\Http\Notifications\NotificationsResponseFactory;
use App\Http\Requests\NotificationChannelRequest;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationChannelReaderV1;
use Illuminate\Http\JsonResponse;

final class NotificationChannelController extends Controller implements NotificationsHttpRuntimeV1
{
    public function __construct(private readonly NotificationChannelReaderV1 $reader, private readonly NotificationsResponseFactory $responses) {}

    public function __invoke(NotificationChannelRequest $request): JsonResponse
    {
        return $this->responses->channel($this->reader->read($request->subjectKey(), $request->observedAt()));
    }
}
