<?php

namespace Appart\Modules\RealEstateCatalog\Application\BusinessYear;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;

final readonly class PropertyDecisionOccurredAt
{
    private function __construct(private DateTimeImmutable $value) {}

    public static function fromString(string $value): self
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d{1,6})?(?:Z|[+-]\d{2}:\d{2})$/', $value) !== 1) {
            throw new InvalidArgumentException('The Property decision occurredAt must be an absolute ISO-8601 instant.');
        }

        try {
            $instant = new DateTimeImmutable($value);
            $errors = DateTimeImmutable::getLastErrors();
            if (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
                throw new InvalidArgumentException('The Property decision occurredAt is invalid.');
            }
        } catch (Exception $error) {
            throw new InvalidArgumentException('The Property decision occurredAt is invalid.', previous: $error);
        }

        return new self($instant);
    }

    public function instant(): DateTimeImmutable
    {
        return $this->value;
    }
}
