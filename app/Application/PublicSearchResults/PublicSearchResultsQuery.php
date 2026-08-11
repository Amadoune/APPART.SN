<?php

namespace App\Application\PublicSearchResults;

use InvalidArgumentException;

final readonly class PublicSearchResultsQuery
{
    public function __construct(
        public int $limit = 12,
        public ?string $afterCanonicalPath = null,
        public ?string $transaction = null,
        public ?string $city = null,
        public ?string $propertyType = null,
    ) {
        if ($this->limit < 1 || $this->limit > 24) {
            throw new InvalidArgumentException('Public search result limit must be between 1 and 24.');
        }

        if ($this->afterCanonicalPath === '') {
            throw new InvalidArgumentException('Public search cursor cannot be empty.');
        }
        if ($transaction !== null && ! in_array($transaction, ['sale', 'rent'], true)) {
            throw new InvalidArgumentException('Invalid public transaction filter.');
        }
        foreach (['city' => $city, 'propertyType' => $propertyType] as $field => $value) {
            if ($value !== null && ($value === '' || mb_strlen($value) > 80)) {
                throw new InvalidArgumentException("Invalid public {$field} filter.");
            }
        }
    }
}
