<?php

namespace App\Application\PublicProjectionOutbox;

final readonly class PublicProjectionOutboxClaimBatch
{
    /** @param list<PublicProjectionOutboxRecord> $records */
    public function __construct(public PublicProjectionOutboxClaimResult $result, public array $records) {}
}
