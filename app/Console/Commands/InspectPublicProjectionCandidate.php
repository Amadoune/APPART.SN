<?php

namespace App\Console\Commands;

use App\Application\ProjectionRebuildRuntimeSource\Contract\InspectablePublicProjectionCandidateFactory;
use App\Application\ProjectionRuntimeSource\Contract\CandidatePublicListingProjectionSource;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use Illuminate\Console\Command;

final class InspectPublicProjectionCandidate extends Command
{
    protected $signature = 'public-projection:inspect-candidate {listingId} {generationId}';

    protected $description = 'Inspect candidate readiness without creating a Candidate or Generation.';

    public function handle(InspectablePublicProjectionCandidateFactory $factory, CandidatePublicListingProjectionSource $source): int
    {
        $listingId = (string) $this->argument('listingId');
        $generationId = PublicProjectionGenerationId::fromString((string) $this->argument('generationId'));
        $assembled = $source->inspectForGeneration($listingId, $generationId);
        $candidate = $factory->inspect($listingId, $generationId);
        $this->line(json_encode([
            'candidateStatus' => $candidate->status->value,
            'sourceStatus' => $candidate->sourceStatus?->value,
            'readiness' => $candidate->readiness?->value,
            'publicGeographyVersion' => $assembled->sources?->publicGeographyVersion,
            'publicMediaVersion' => $assembled->sources?->publicMediaVersion,
            'recordBuiltInMemory' => $candidate->record !== null,
            'persisted' => false,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        return self::SUCCESS;
    }
}
