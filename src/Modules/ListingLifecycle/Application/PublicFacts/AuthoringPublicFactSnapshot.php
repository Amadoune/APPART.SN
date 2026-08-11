<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicFacts;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class AuthoringPublicFactSnapshot
{
    public function __construct(
        public string $listingId,
        public int $authoringVersion,
        public PublicTransactionKind $transactionKind,
        public string $sourceIntentId,
        public string $sourceChecksum,
        public DateTimeImmutable $observedAt,
    ) {
        if ($authoringVersion < 1 || preg_match('/^[0-9a-f-]{36}$/', $listingId) !== 1 || preg_match('/^[0-9a-f-]{36}$/', $sourceIntentId) !== 1 || preg_match('/^[0-9a-f]{64}$/', $sourceChecksum) !== 1) {
            throw new InvalidArgumentException('Invalid Authoring public fact snapshot.');
        }
    }

    public function checksum(): string
    {
        return hash('sha256', implode('|', [$this->listingId, (string) $this->authoringVersion, $this->transactionKind->value, $this->sourceIntentId, $this->sourceChecksum, $this->observedAt->format('Y-m-d\TH:i:s.uP')]));
    }
}
