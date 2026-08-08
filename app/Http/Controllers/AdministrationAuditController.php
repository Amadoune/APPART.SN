<?php

namespace App\Http\Controllers;

use App\Http\AdministrationConsole\AdministrationConsoleHttpRuntimeV1;
use App\Http\AdministrationConsole\AdministrationConsoleResponseFactory;
use App\Http\Requests\AdministrationAuditRequest;
use Appart\Modules\AdministrationConsole\Application\PublicRead\Contract\AdministrationAuditReaderV1;
use Illuminate\Http\JsonResponse;

final class AdministrationAuditController extends Controller implements AdministrationConsoleHttpRuntimeV1
{
    public function __construct(private readonly AdministrationAuditReaderV1 $reader, private readonly AdministrationConsoleResponseFactory $responses) {}

    public function __invoke(AdministrationAuditRequest $request): JsonResponse
    {
        return $this->responses->audit($this->reader->read($request->subjectKey(), $request->observedAt()));
    }
}
