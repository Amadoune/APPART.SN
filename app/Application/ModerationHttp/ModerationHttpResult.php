<?php

namespace App\Application\ModerationHttp;

final readonly class ModerationHttpResult
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public ModerationHttpStatus $status,
        public array $data = [],
    ) {}
}
