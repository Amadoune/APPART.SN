<?php

namespace Appart\Modules\SearchDiscovery\Domain\ValueObject;

use Appart\Modules\SearchDiscovery\Domain\Exception\InvalidSearchValue;

final readonly class SourceRevisionSet
{
    /** @var array<string, SourceRevision> */
    private array $revisions;

    public function __construct(SourceRevision $listing, SourceRevision $property, SourceRevision $media)
    {
        if ($listing->source !== SourceKind::Listing || $property->source !== SourceKind::Property || $media->source !== SourceKind::Media) {
            throw InvalidSearchValue::field('source_revision_set');
        }

        $this->revisions = [
            SourceKind::Listing->value => $listing,
            SourceKind::Property->value => $property,
            SourceKind::Media->value => $media,
        ];
    }

    public function for(SourceKind $source): SourceRevision
    {
        return $this->revisions[$source->value];
    }

    /** @return list<SourceRevision> */
    public function all(): array
    {
        return array_values($this->revisions);
    }
}
