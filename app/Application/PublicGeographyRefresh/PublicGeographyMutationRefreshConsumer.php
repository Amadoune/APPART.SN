<?php

namespace App\Application\PublicGeographyRefresh;

use App\Application\PublicGeographyMaterialization\PublicGeographyMaterializationStatus;
use App\Application\PublicGeographyRefresh\Contract\AffectedPublicGeographyTerminalReaderV1;

final readonly class PublicGeographyMutationRefreshConsumer
{
    public function __construct(private AffectedPublicGeographyTerminalReaderV1 $affected, private RefreshPublicGeographyTerminalV2 $refresh, private int $pageSize = 100) {}

    public function consume(string $mutatedPlaceId, string $sourceEventId): bool
    {
        $cursor = null;
        do {
            $page = $this->affected->read($mutatedPlaceId, $cursor, $this->pageSize);
            if ($page->status === AffectedPublicGeographyTerminalStatus::Empty) {
                return true;
            }
            if ($page->status !== AffectedPublicGeographyTerminalStatus::Available) {
                return false;
            }
            foreach ($page->terminalPlaceIds as $terminalPlaceId) {
                $causation = hash('sha256', $sourceEventId.'|'.$terminalPlaceId.'|refresh-contract-v1');
                $result = $this->refresh->refresh($terminalPlaceId, $causation);
                if (! in_array($result->status, [PublicGeographyMaterializationStatus::Applied, PublicGeographyMaterializationStatus::AlreadyApplied, PublicGeographyMaterializationStatus::RejectedObsolete], true)) {
                    return false;
                }
            }
            $cursor = $page->nextCursor;
        } while ($cursor !== null);

        return true;
    }
}
