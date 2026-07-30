<?php

namespace Appart\Modules\ModerationReports\Domain\ValueObject;

use Appart\Modules\ModerationReports\Domain\Exception\InvalidModerationValue;

final readonly class ReporterId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim($value);
        if ($value === '' || mb_strlen($value) > 100) {
            throw InvalidModerationValue::field('reporter_id');
        }

        return new self($value);
    }
}
