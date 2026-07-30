<?php

namespace App\Application\PublicProjectionStore\Contract;

use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionWriteResult;

interface PublicListingProjectionWriter
{
    /** Applies a current snapshot to the active generation without deciding its canonical or content. */
    public function applyCurrent(PublicListingProjectionRecord $record): PublicProjectionWriteResult;

    /** Atomically reserves the previous current path as historical and applies the decided replacement. */
    public function replaceCanonical(string $previousCanonicalPath, PublicListingProjectionRecord $replacement): PublicProjectionWriteResult;

    /** Applies a versioned technical removal to the active generation. */
    public function applyTombstone(PublicListingProjectionRecord $tombstone): PublicProjectionWriteResult;

    /** Writes a current snapshot only into the isolated candidate generation. */
    public function writeCandidate(PublicListingProjectionRecord $record): PublicProjectionWriteResult;
}
