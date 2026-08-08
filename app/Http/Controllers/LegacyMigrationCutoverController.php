<?php

namespace App\Http\Controllers;

use App\Http\LegacyMigration\LegacyMigrationHttpRuntimeV1;
use App\Http\LegacyMigration\LegacyMigrationResponseFactory;
use App\Http\Requests\LegacyMigrationCutoverRequest;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationCutoverReaderV1;
use Illuminate\Http\JsonResponse;

final class LegacyMigrationCutoverController extends Controller implements LegacyMigrationHttpRuntimeV1
{
    public function __construct(private readonly LegacyMigrationCutoverReaderV1 $reader, private readonly LegacyMigrationResponseFactory $responses) {}

    public function __invoke(LegacyMigrationCutoverRequest $request): JsonResponse
    {
        return $this->responses->cutover($this->reader->read($request->subjectKey(), $request->observedAt()));
    }
}
