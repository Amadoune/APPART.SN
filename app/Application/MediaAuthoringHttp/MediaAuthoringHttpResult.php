<?php

namespace App\Application\MediaAuthoringHttp;

final readonly class MediaAuthoringHttpResult
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public MediaAuthoringHttpStatus $status,
        public array $data = [],
    ) {}
}
