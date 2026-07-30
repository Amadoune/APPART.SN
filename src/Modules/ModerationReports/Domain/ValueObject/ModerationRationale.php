<?php

namespace Appart\Modules\ModerationReports\Domain\ValueObject;

use Appart\Modules\ModerationReports\Domain\Exception\InvalidModerationValue;

final readonly class ModerationRationale
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim((string) preg_replace('/\s+/u', ' ', $value));
        if (mb_strlen($value) < 3 || mb_strlen($value) > 2000) {
            throw InvalidModerationValue::field('moderation_rationale');
        }

        return new self($value);
    }
}
