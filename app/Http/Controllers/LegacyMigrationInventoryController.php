<?php

namespace App\Http\Controllers;

use App\Http\LegacyMigration\LegacyMigrationHttpRuntimeV1;
use App\Http\LegacyMigration\LegacyMigrationResponseFactory;
use App\Http\Requests\LegacyMigrationInventoryRequest;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationInventoryReaderV1;
use Illuminate\Http\JsonResponse;

final class LegacyMigrationInventoryController extends Controller implements LegacyMigrationHttpRuntimeV1
{
    public function __construct(private readonly LegacyMigrationInventoryReaderV1 $reader, private readonly LegacyMigrationResponseFactory $responses) {}

    public function __invoke(LegacyMigrationInventoryRequest $request): JsonResponse
    {
        return $this->responses->inventory($this->reader->read($request->subjectKey(), $request->observedAt()));
    }
}
