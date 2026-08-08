<?php

namespace App\Http\Controllers;

use App\Http\AdministrationConsole\AdministrationConsoleHttpRuntimeV1;
use App\Http\AdministrationConsole\AdministrationConsoleResponseFactory;
use App\Http\Requests\AdministrationQueueRequest;
use Appart\Modules\AdministrationConsole\Application\PublicRead\Contract\AdministrationQueueReaderV1;
use Illuminate\Http\JsonResponse;

final class AdministrationQueueController extends Controller implements AdministrationConsoleHttpRuntimeV1
{
    public function __construct(private readonly AdministrationQueueReaderV1 $reader, private readonly AdministrationConsoleResponseFactory $responses) {}

    public function __invoke(AdministrationQueueRequest $request): JsonResponse
    {
        return $this->responses->queue($this->reader->read($request->subjectKey(), $request->observedAt()));
    }
}
