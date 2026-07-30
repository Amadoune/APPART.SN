<?php

namespace Tests\Support;

use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSourceDecision;
use Appart\Modules\ContentSeo\Domain\Model\CanonicalHistoryEntry;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PropertySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SearchSeoSource;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalDisposition;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\PropertySeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SearchSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceKind;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceRevision;
use DateTimeImmutable;

final class ContentSeoSourceSnapshotFixture
{
    public static function make(int $version = 1, string $headline = 'Appartement à Dakar'): ContentSeoSourceDecision
    {
        $id = ListingId::fromString('99400000-0000-4000-8000-000000000001');
        $at = new DateTimeImmutable('2026-07-19T10:00:00+00:00');
        $revision = static fn (SeoSourceKind $kind, int $suffix): SeoSourceRevision => SeoSourceRevision::create($kind, $version, sprintf('99400000-0000-4000-8000-%012d', $suffix), sprintf('99500000-0000-4000-8000-%012d', $version), $at);

        return new ContentSeoSourceDecision(
            sprintf('99600000-0000-4000-8000-%012d', $version),
            $id,
            $version,
            new ListingSeoSource($id, ListingSeoState::Published, $headline, 'Description publique déjà décidée', 'annonces/appartement-dakar', $revision(SeoSourceKind::Listing, 1), $at),
            new SearchSeoSource($id, SearchSeoState::Public, $revision(SeoSourceKind::Search, 2)),
            new PropertySeoSource($id, PropertySeoState::Available, 'Appartement', 'Dakar', $revision(SeoSourceKind::Property, 3)),
            [new CanonicalHistoryEntry(CanonicalUrl::fromString('https://appart.sn/annonces/appartement-dakar'), CanonicalDisposition::Current, $at)],
            $at,
        );
    }
}
