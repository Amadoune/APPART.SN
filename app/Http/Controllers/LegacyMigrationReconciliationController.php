<?php

namespace App\Http\Controllers;

use App\Http\LegacyMigration\LegacyMigrationHttpRuntimeV1;
use App\Http\LegacyMigration\LegacyMigrationResponseFactory;
use App\Http\Requests\LegacyMigrationReconciliationRequest;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationReconciliationReaderV1;
use Illuminate\Http\JsonResponse;

final class LegacyMigrationReconciliationController extends Controller implements LegacyMigrationHttpRuntimeV1
{
    public function __construct(private readonly LegacyMigrationReconciliationReaderV1 $reader, private readonly LegacyMigrationResponseFactory $responses) {}

    public function __invoke(LegacyMigrationReconciliationRequest $request): JsonResponse
    {
        return $this->responses->reconciliation($this->reader->read($request->subjectKey(), $request->observedAt()));
    }
}
