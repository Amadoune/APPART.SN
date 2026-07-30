<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class AdministrativeActionHistoricalMirrorOccurredAt
{
    private function __construct(public DateTimeImmutable $value) {}

    public static function fromExplicitUtc(DateTimeImmutable $value): self
    {
        if ($value->getOffset() !== 0) {
            throw new InvalidArgumentException('The historical mirror occurrence time must be explicit UTC.');
        }

        return new self($value);
    }

    public function canonical(): string
    {
        return $this->value->format('Y-m-d\\TH:i:s.u\\Z');
    }
}
