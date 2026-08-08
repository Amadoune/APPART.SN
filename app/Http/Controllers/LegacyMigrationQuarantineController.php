<?php

namespace App\Http\Controllers;

use App\Http\LegacyMigration\LegacyMigrationHttpRuntimeV1;
use App\Http\LegacyMigration\LegacyMigrationResponseFactory;
use App\Http\Requests\LegacyMigrationQuarantineRequest;
use Appart\Modules\LegacyMigration\Application\PublicRead\Contract\LegacyMigrationQuarantineReaderV1;
use Illuminate\Http\JsonResponse;

final class LegacyMigrationQuarantineController extends Controller implements LegacyMigrationHttpRuntimeV1
{
    public function __construct(private readonly LegacyMigrationQuarantineReaderV1 $reader, private readonly LegacyMigrationResponseFactory $responses) {}

    public function __invoke(LegacyMigrationQuarantineRequest $request): JsonResponse
    {
        return $this->responses->quarantine($this->reader->read($request->subjectKey(), $request->observedAt()));
    }
}
