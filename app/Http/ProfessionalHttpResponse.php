<?php

namespace App\Http;

final readonly class ProfessionalHttpResponse
{
    /** @param array<string, string> $body */
    public function __construct(
        public array $body,
        public int $status,
    ) {}
}
