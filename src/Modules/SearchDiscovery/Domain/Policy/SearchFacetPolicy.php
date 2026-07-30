<?php

namespace Appart\Modules\SearchDiscovery\Domain\Policy;

use Appart\Modules\SearchDiscovery\Domain\Exception\ForbiddenSearchFacet;
use Appart\Modules\SearchDiscovery\Domain\Exception\SearchViolation;
use Appart\Modules\SearchDiscovery\Domain\Model\SearchFacet;

final readonly class SearchFacetPolicy
{
    /** @var array<string, bool> */
    private const ALLOWED = [
        'amenity' => true,
        'feature' => true,
        'category' => false,
        'city' => false,
        'district' => false,
        'has_image' => false,
        'property_type' => false,
    ];

    /**
     * @param  list<SearchFacet>  $facets
     * @return list<SearchFacet>
     */
    public function govern(array $facets): array
    {
        $accepted = [];
        $singleValues = [];

        foreach ($facets as $facet) {
            $key = $facet->key->value;
            if (! array_key_exists($key, self::ALLOWED)) {
                throw new ForbiddenSearchFacet("Facet {$key} is not public or governed.");
            }
            if (! self::ALLOWED[$key] && isset($singleValues[$key]) && $singleValues[$key] !== $facet->value->value) {
                throw SearchViolation::duplicate('single-valued facet');
            }
            $singleValues[$key] = $facet->value->value;
            $accepted[$key.'|'.$facet->value->value.'|'.$facet->source->value] = $facet;
        }

        ksort($accepted);

        return array_values($accepted);
    }
}
