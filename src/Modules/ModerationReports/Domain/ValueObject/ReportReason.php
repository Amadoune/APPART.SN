<?php

namespace Appart\Modules\ModerationReports\Domain\ValueObject;

use Appart\Modules\ModerationReports\Domain\Exception\InvalidModerationValue;

final readonly class ReportReason
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim((string) preg_replace('/\s+/u', ' ', $value));
        if (mb_strlen($value) < 3 || mb_strlen($value) > 1000) {
            throw InvalidModerationValue::field('report_reason');
        }

        return new self($value);
    }
}
