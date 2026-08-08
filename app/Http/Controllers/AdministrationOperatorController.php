<?php

namespace App\Http\Controllers;

use App\Http\AdministrationConsole\AdministrationConsoleHttpRuntimeV1;
use App\Http\AdministrationConsole\AdministrationConsoleResponseFactory;
use App\Http\Requests\AdministrationOperatorRequest;
use Appart\Modules\AdministrationConsole\Application\PublicRead\Contract\AdministrationOperatorReaderV1;
use Illuminate\Http\JsonResponse;

final class AdministrationOperatorController extends Controller implements AdministrationConsoleHttpRuntimeV1
{
    public function __construct(private readonly AdministrationOperatorReaderV1 $reader, private readonly AdministrationConsoleResponseFactory $responses) {}

    public function __invoke(AdministrationOperatorRequest $request): JsonResponse
    {
        return $this->responses->operator($this->reader->read($request->subjectKey(), $request->observedAt()));
    }
}
