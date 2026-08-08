<?php

namespace Appart\Modules\SecurityCompliance\Application\PublicRead;

use InvalidArgumentException;

final readonly class SecurityComplianceSubjectKey
{
    public function __construct(public string $value)
    {
        if ($value === '' || trim($value) !== $value || strlen($value) > 255) {
            throw new InvalidArgumentException('The Security Compliance subject key must be canonical.');
        }
    }

    public function canonical(): string
    {
        return $this->value;
    }
}
