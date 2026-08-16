<?php

namespace App\Application\ActiveGenerationBootstrap;

use App\Application\ActiveGenerationBootstrap\Contract\ActiveGenerationBootstrapStateReader;
use App\Application\ActiveGenerationBootstrap\Contract\BootstrapActiveProjectionGenerationV1;
use App\Application\ActiveGenerationReader\ActiveGenerationReadStatus;
use App\Application\ActiveGenerationReader\Contract\ActiveGenerationReader;
use App\Application\ProjectionRebuildRuntimeSource\Contract\InspectablePublicProjectionCandidateFactory;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionGenerationManager;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionGenerationValidator;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifest;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifestEntry;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationTransition;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuilder;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuildScope;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use Throwable;

final readonly class DeterministicActiveProjectionGenerationBootstrapV1 implements BootstrapActiveProjectionGenerationV1
{
    public function __construct(
        private ActiveGenerationBootstrapStateReader $states,
        private InspectablePublicProjectionCandidateFactory $candidates,
        private PublicProjectionGenerationManager $generations,
        private PublicProjectionRebuilder $rebuilder,
        private PublicProjectionGenerationValidator $validator,
        private ActiveGenerationReader $activeReader,
    ) {}

    public function bootstrap(BootstrapActiveProjectionGenerationCommand $command): BootstrapActiveProjectionGenerationResult
    {
        try {
            $generationId = PublicProjectionGenerationId::fromString($command->generationId);
            $state = $this->states->read();
            if ($state->activeCount > 0 && $state->activeGenerationId !== $command->generationId) {
                return $this->result(BootstrapActiveProjectionGenerationStatus::ActiveGenerationExists, $command, state: $state);
            }
            $foreignCandidates = array_diff($state->candidateGenerationIds, [$command->generationId]);
            if ($foreignCandidates !== []) {
                return $this->result(BootstrapActiveProjectionGenerationStatus::CandidateGenerationExists, $command, state: $state);
            }

            $entries = [];
            foreach ($command->listingIds as $listingId) {
                $candidate = $this->candidates->inspect($listingId, $generationId);
                if ($candidate->record === null || $candidate->record->listingId !== $listingId || ! $candidate->record->generationId->equals($generationId)) {
                    return $this->result(BootstrapActiveProjectionGenerationStatus::CandidateRejected, $command, state: $state);
                }
                $entries[] = new PublicProjectionGenerationManifestEntry($listingId, $candidate->record->watermark);
            }
            $manifest = new PublicProjectionGenerationManifest($entries);

            if ($state->activeGenerationId === $command->generationId) {
                $validation = $this->validator->validate($generationId, $manifest);
                $active = $this->activeReader->read();
                if (! $validation->isValid() || $active->status !== ActiveGenerationReadStatus::Found || $active->generation?->id->value !== $command->generationId) {
                    return $this->result(BootstrapActiveProjectionGenerationStatus::ActiveReaderFailed, $command, manifestCount: $manifest->count(), validationPassed: $validation->isValid(), activeReaderStatus: $active->status->value, state: $this->states->read());
                }

                return $this->result(BootstrapActiveProjectionGenerationStatus::AlreadyApplied, $command, alreadyApplied: $manifest->count(), manifestCount: $manifest->count(), validationPassed: true, activeReaderStatus: $active->status->value, state: $this->states->read());
            }

            $created = $this->generations->createCandidate($generationId);
            if (! in_array($created, [PublicProjectionGenerationTransition::Applied, PublicProjectionGenerationTransition::AlreadyApplied], true)) {
                return $this->result(BootstrapActiveProjectionGenerationStatus::CandidateGenerationExists, $command, manifestCount: $manifest->count(), state: $this->states->read());
            }
            $processed = $applied = $already = 0;
            $checkpoint = null;
            do {
                $report = $this->rebuilder->runOnce($generationId, PublicProjectionRebuildScope::listings($command->listingIds), $checkpoint);
                $processed += $report->processed;
                $applied += $report->applied;
                $already += $report->alreadyApplied;
                if ($report->missing > 0 || $report->rejectedListingIds !== []) {
                    return $this->result(BootstrapActiveProjectionGenerationStatus::RebuildFailed, $command, $processed, $applied, $already, $manifest->count(), state: $this->states->read());
                }
                $checkpoint = $report->nextCheckpoint;
            } while ($checkpoint !== null);
            if ($applied + $already < 1) {
                return $this->result(BootstrapActiveProjectionGenerationStatus::RebuildFailed, $command, $processed, $applied, $already, $manifest->count(), state: $this->states->read());
            }

            $validation = $this->validator->validate($generationId, $manifest);
            if (! $validation->isValid()) {
                return $this->result(BootstrapActiveProjectionGenerationStatus::ManifestInvalid, $command, $processed, $applied, $already, $manifest->count(), false, state: $this->states->read());
            }
            $activation = $this->generations->activate($generationId, $manifest);
            if (! in_array($activation, [PublicProjectionGenerationTransition::Applied, PublicProjectionGenerationTransition::AlreadyApplied], true)) {
                return $this->result(BootstrapActiveProjectionGenerationStatus::ActivationFailed, $command, $processed, $applied, $already, $manifest->count(), true, state: $this->states->read());
            }
            $active = $this->activeReader->read();
            if ($active->status !== ActiveGenerationReadStatus::Found || $active->generation?->id->value !== $command->generationId) {
                return $this->result(BootstrapActiveProjectionGenerationStatus::ActiveReaderFailed, $command, $processed, $applied, $already, $manifest->count(), true, $active->status->value, $this->states->read());
            }

            return $this->result(BootstrapActiveProjectionGenerationStatus::Applied, $command, $processed, $applied, $already, $manifest->count(), true, $active->status->value, $this->states->read());
        } catch (Throwable) {
            return $this->result(BootstrapActiveProjectionGenerationStatus::DependencyUnavailable, $command);
        }
    }

    private function result(
        BootstrapActiveProjectionGenerationStatus $status,
        BootstrapActiveProjectionGenerationCommand $command,
        int $processed = 0,
        int $applied = 0,
        int $alreadyApplied = 0,
        int $manifestCount = 0,
        bool $validationPassed = false,
        ?string $activeReaderStatus = null,
        ?ActiveGenerationBootstrapState $state = null,
    ): BootstrapActiveProjectionGenerationResult {
        return new BootstrapActiveProjectionGenerationResult($status, $command->generationId, $command->scopeChecksum(), $processed, $applied, $alreadyApplied, $manifestCount, $validationPassed, $activeReaderStatus, $state);
    }
}
