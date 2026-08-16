<?php

namespace App\Application\PublicGeographyRefresh;

use App\Application\PublicGeographyMaterialization\PublicGeographyDecisionV2Assembler;
use App\Application\PublicGeographyMaterialization\PublicGeographyHierarchyReader;
use App\Application\PublicGeographyMaterialization\PublicGeographyMaterializationResult;
use App\Application\PublicGeographyMaterialization\PublicGeographyMaterializationStatus;
use App\Application\PublicGeographySource\Contract\PublicGeographyDecisionWriter;
use App\Application\PublicGeographySource\PublicGeographyWriteResult;
use Throwable;

final readonly class RefreshPublicGeographyTerminalV2
{
    public function __construct(private PublicGeographyHierarchyReader $hierarchy, private PublicGeographyDecisionV2Assembler $assembler, private PublicGeographyDecisionWriter $writer) {}

    public function refresh(string $terminalPlaceId, string $causationKey): PublicGeographyMaterializationResult
    {
        try {
            $decision = $this->assembler->assemble($this->hierarchy->read($terminalPlaceId), $causationKey);
            $status = match ($this->writer->store($decision)) {
                PublicGeographyWriteResult::Applied => PublicGeographyMaterializationStatus::Applied,
                PublicGeographyWriteResult::AlreadyApplied => PublicGeographyMaterializationStatus::AlreadyApplied,
                PublicGeographyWriteResult::RejectedObsolete => PublicGeographyMaterializationStatus::RejectedObsolete,
                PublicGeographyWriteResult::Divergent => PublicGeographyMaterializationStatus::Divergent,
            };

            return new PublicGeographyMaterializationResult($status, $terminalPlaceId, $decision->revision->watermarkVersion());
        } catch (Throwable) {
            return new PublicGeographyMaterializationResult(PublicGeographyMaterializationStatus::DependencyUnavailable, $terminalPlaceId);
        }
    }
}
