<?php

namespace App\Http\Controllers;

use App\Http\Notifications\NotificationsHttpRuntimeV1;
use App\Http\Notifications\NotificationsResponseFactory;
use App\Http\Requests\NotificationTemplateRequest;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationTemplateReaderV1;
use Illuminate\Http\JsonResponse;

final class NotificationTemplateController extends Controller implements NotificationsHttpRuntimeV1
{
    public function __construct(private readonly NotificationTemplateReaderV1 $reader, private readonly NotificationsResponseFactory $responses) {}

    public function __invoke(NotificationTemplateRequest $request): JsonResponse
    {
        return $this->responses->template($this->reader->read($request->subjectKey(), $request->observedAt()));
    }
}
