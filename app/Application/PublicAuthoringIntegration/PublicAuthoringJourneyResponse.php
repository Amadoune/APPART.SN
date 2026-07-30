<?php

namespace App\Application\PublicAuthoringIntegration;

final readonly class PublicAuthoringJourneyResponse
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public PublicAuthoringJourneyStatus $status,
        public array $data = [],
    ) {}
}
