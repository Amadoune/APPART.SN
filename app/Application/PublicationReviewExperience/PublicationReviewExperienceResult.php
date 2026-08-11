<?php

namespace App\Application\PublicationReviewExperience;

final readonly class PublicationReviewExperienceResult
{
    /** @param array<string, mixed> $data */
    public function __construct(public PublicationReviewExperienceStatus $status, public array $data = []) {}
}
