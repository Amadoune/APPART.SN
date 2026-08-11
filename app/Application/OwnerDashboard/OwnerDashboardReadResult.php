<?php

namespace App\Application\OwnerDashboard;

final readonly class OwnerDashboardReadResult
{
    /** @param list<OwnerDashboardListing> $listings */
    private function __construct(
        public OwnerDashboardReadStatus $status,
        public array $listings = [],
    ) {}

    /** @param list<OwnerDashboardListing> $listings */
    public static function available(array $listings): self
    {
        return new self(OwnerDashboardReadStatus::Available, $listings);
    }

    public static function empty(): self
    {
        return new self(OwnerDashboardReadStatus::Empty);
    }

    public static function unavailable(): self
    {
        return new self(OwnerDashboardReadStatus::Unavailable);
    }
}
