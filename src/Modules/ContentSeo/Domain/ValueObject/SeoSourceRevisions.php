<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;

final readonly class SeoSourceRevisions
{
    /** @var array<string, SeoSourceRevision> */
    private array $items;

    public function __construct(SeoSourceRevision $listing, SeoSourceRevision $search, SeoSourceRevision $property)
    {
        if ($listing->source !== SeoSourceKind::Listing || $search->source !== SeoSourceKind::Search || $property->source !== SeoSourceKind::Property) {
            throw InvalidSeoValue::field('source_revisions');
        }
        $this->items = [$listing->source->value => $listing, $search->source->value => $search, $property->source->value => $property];
    }

    public function for(SeoSourceKind $source): SeoSourceRevision
    {
        return $this->items[$source->value];
    }

    /** @return list<SeoSourceRevision> */
    public function all(): array
    {
        return array_values($this->items);
    }
}
