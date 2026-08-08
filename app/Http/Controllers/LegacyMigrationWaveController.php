<?php

namespace App\Http\Controllers;

use App\Http\LegacyMigration\LegacyMigrationHttpRuntimeV1;
use App\Http\LegacyMigration\LegacyMigrationResponseFactory;
use App\Http\Requests\LegacyMigrationWaveRequest;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationWaveReaderV1;
use Illuminate\Http\JsonResponse;

final class LegacyMigrationWaveController extends Controller implements LegacyMigrationHttpRuntimeV1
{
    public function __construct(private readonly LegacyMigrationWaveReaderV1 $reader, private readonly LegacyMigrationResponseFactory $responses) {}

    public function __invoke(LegacyMigrationWaveRequest $request): JsonResponse
    {
        return $this->responses->wave($this->reader->read($request->subjectKey(), $request->observedAt()));
    }
}
