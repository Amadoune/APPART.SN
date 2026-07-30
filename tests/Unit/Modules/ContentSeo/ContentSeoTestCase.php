<?php

namespace Tests\Unit\Modules\ContentSeo;

use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PropertySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SearchSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SeoMaterial;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\SeoGenerationPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\PropertySeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SearchSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceKind;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceRevision;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

abstract class ContentSeoTestCase extends TestCase
{
    protected function listingId(int $suffix = 1): ListingId
    {
        return ListingId::fromString(sprintf('70000000-0000-4000-8000-%012d', $suffix));
    }

    protected function sources(int $version, ListingSeoState $listingState = ListingSeoState::Published, SearchSeoState $searchState = SearchSeoState::Public, PropertySeoState $propertyState = PropertySeoState::Available, ?ListingId $id = null): array
    {
        $id ??= $this->listingId();

        return [
            new ListingSeoSource($id, $listingState, 'Appartement moderne à Dakar', 'Découvrez cet appartement moderne, lumineux et bien situé au cœur de Dakar pour votre prochain logement.', 'annonces/appartement-moderne-dakar', $this->revision(SeoSourceKind::Listing, $version)),
            new SearchSeoSource($id, $searchState, $this->revision(SeoSourceKind::Search, $version)),
            new PropertySeoSource($id, $propertyState, 'Appartement', 'Dakar', $this->revision(SeoSourceKind::Property, $version)),
        ];
    }

    protected function material(array $sources): SeoMaterial
    {
        return (new SeoGenerationPolicy(new CanonicalPolicy))->generate(...$sources);
    }

    protected function revision(SeoSourceKind $kind, int $version): SeoSourceRevision
    {
        $offset = match ($kind) {
            SeoSourceKind::Listing => 1, SeoSourceKind::Search => 2, SeoSourceKind::Property => 3
        };

        return SeoSourceRevision::create(
            $kind,
            $version,
            sprintf('80000000-0000-4000-8000-%012d', $version * 10 + $offset),
            sprintf('81000000-0000-4000-8000-%012d', $version),
            $this->at($version),
        );
    }

    protected function at(int $minute): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-17T10:00:00+00:00')->modify("+{$minute} minutes");
    }
}
