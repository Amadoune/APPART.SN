<?php

namespace Appart\Modules\SearchDiscovery\Domain\Model;

use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchFacetKey;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchFacetValue;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceKind;

final readonly class SearchFacet
{
    public function __construct(
        public SearchFacetKey $key,
        public SearchFacetValue $value,
        public SourceKind $source,
    ) {}

    public function equals(self $other): bool
    {
        return $this->key->equals($other->key)
            && $this->value->equals($other->value)
            && $this->source === $other->source;
    }
}
