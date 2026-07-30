<?php

namespace App\Application\DecisionTimeSource;

use DateTimeImmutable;

final readonly class DecisionTimeReadResult
{
    private function __construct(
        public string $listingId,
        public DecisionTimeReadStatus $status,
        public ?DateTimeImmutable $decisionAt,
    ) {}

    public static function found(string $listingId, DateTimeImmutable $decisionAt): self
    {
        return new self($listingId, DecisionTimeReadStatus::Found, $decisionAt);
    }

    public static function missing(string $listingId): self
    {
        return new self($listingId, DecisionTimeReadStatus::Missing, null);
    }

    public static function corrupted(string $listingId): self
    {
        return new self($listingId, DecisionTimeReadStatus::Corrupted, null);
    }
}
