<?php

namespace App\Application\PublicGeographySource;

final readonly class PublicGeographyReadResult
{
    private function __construct(public string $placeId, public PublicGeographyReadStatus $status, public PublicGeographyDecision|PublicGeographyDecisionV2|null $decision) {}

    public static function found(string $id, PublicGeographyDecision $decision): self
    {
        return new self($id, PublicGeographyReadStatus::Found, $decision);
    }

    public static function foundV2(string $id, PublicGeographyDecisionV2 $decision): self
    {
        return new self($id, PublicGeographyReadStatus::Found, $decision);
    }

    public static function missing(string $id): self
    {
        return new self($id, PublicGeographyReadStatus::Missing, null);
    }

    public static function corrupted(string $id): self
    {
        return new self($id, PublicGeographyReadStatus::Corrupted, null);
    }
}
