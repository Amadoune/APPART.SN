<?php

namespace App\Console\Commands;

use App\Application\ActiveGenerationBootstrap\BootstrapActiveProjectionGenerationCommand;
use App\Application\ActiveGenerationBootstrap\BootstrapActiveProjectionGenerationStatus;
use App\Application\ActiveGenerationBootstrap\Contract\ActiveGenerationBootstrapStateReader;
use App\Application\ActiveGenerationBootstrap\Contract\BootstrapActiveProjectionGenerationV1;
use App\Application\ActiveGenerationReader\Contract\ActiveGenerationReader;
use App\Application\ProjectionRebuildRuntimeSource\Contract\InspectablePublicProjectionCandidateFactory;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use Illuminate\Console\Command;
use Throwable;

final class BootstrapActiveProjectionGeneration extends Command
{
    protected $signature = 'appart:projection:generation:bootstrap
        {generationId? : Explicit UUIDv4 GenerationId}
        {--generation= : Explicit UUIDv4 GenerationId}
        {--listing=* : Explicit non-empty Listing scope}
        {--operator= : Audit operator identity}
        {--inspect : Read-only preflight}';

    protected $description = 'Operator-only initial Active Projection Generation bootstrap.';

    public function handle(
        BootstrapActiveProjectionGenerationV1 $bootstrap,
        ActiveGenerationBootstrapStateReader $states,
        ActiveGenerationReader $activeReader,
        InspectablePublicProjectionCandidateFactory $candidates,
    ): int {
        try {
            $generation = (string) ($this->option('generation') ?: $this->argument('generationId'));
            $listings = array_values(array_filter($this->option('listing'), 'is_string'));
            $operator = (string) $this->option('operator');
            $command = new BootstrapActiveProjectionGenerationCommand($generation, $listings, $operator);
            if ((bool) $this->option('inspect')) {
                $generationId = PublicProjectionGenerationId::fromString($generation);
                $candidateResults = [];
                foreach ($listings as $listingId) {
                    $candidate = $candidates->inspect($listingId, $generationId);
                    $candidateResults[$listingId] = [
                        'status' => $candidate->status->value,
                        'sourceStatus' => $candidate->sourceStatus?->value,
                        'readiness' => $candidate->readiness?->value,
                        'record' => $candidate->record !== null,
                    ];
                }
                $this->line(json_encode([
                    'mode' => 'inspect',
                    'generationId' => $generation,
                    'scopeChecksum' => $command->scopeChecksum(),
                    'state' => $states->read(),
                    'activeReader' => $activeReader->read()->status->value,
                    'candidates' => $candidateResults,
                ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

                return self::SUCCESS;
            }
            $result = $bootstrap->bootstrap($command);
            $this->line(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            return in_array($result->status, [BootstrapActiveProjectionGenerationStatus::Applied, BootstrapActiveProjectionGenerationStatus::AlreadyApplied], true) ? self::SUCCESS : self::FAILURE;
        } catch (Throwable) {
            $this->error(json_encode(['status' => BootstrapActiveProjectionGenerationStatus::InvalidInput->value], JSON_THROW_ON_ERROR));

            return self::FAILURE;
        }
    }
}
