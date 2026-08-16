<?php

namespace App\Application\AuthoringDraftResume;

final readonly class AuthoringDraftResumeResult
{
    /** @param array<string, mixed> $snapshot */
    public function __construct(
        public AuthoringDraftResumeStatus $status,
        public array $snapshot = [],
    ) {}
}
