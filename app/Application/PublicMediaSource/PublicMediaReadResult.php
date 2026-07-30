<?php

namespace App\Application\PublicMediaSource;

final readonly class PublicMediaReadResult
{
    private function __construct(
        public string $mediaCollectionId,
        public PublicMediaReadStatus $status,
        public ?PublicMediaDecision $decision,
    ) {}

    public static function found(string $id, PublicMediaDecision $decision): self
    {
        return new self($id, PublicMediaReadStatus::Found, $decision);
    }

    public static function missing(string $id): self
    {
        return new self($id, PublicMediaReadStatus::Missing, null);
    }

    public static function corrupted(string $id): self
    {
        return new self($id, PublicMediaReadStatus::Corrupted, null);
    }
}
