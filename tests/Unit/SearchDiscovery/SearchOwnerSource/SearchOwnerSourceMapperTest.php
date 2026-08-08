<?php

namespace Tests\Unit\SearchDiscovery\SearchOwnerSource;

use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerReadStatus;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerRevisionDecision;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerRevisionState;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerWriteResult;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchOwnerSourceMapper;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\SearchDecisionFixture;

final class SearchOwnerSourceMapperTest extends TestCase
{
    #[Test]
    public function catalogues_are_closed_and_exact(): void
    {
        self::assertSame(['found', 'missing', 'corrupted', 'dependency_unavailable'], array_column(SearchOwnerReadStatus::cases(), 'value'));
        self::assertSame(['applied', 'already_applied', 'divergent_revision', 'version_conflict', 'corrupted', 'dependency_unavailable'], array_column(SearchOwnerWriteResult::cases(), 'value'));
        self::assertSame(['visible', 'hidden', 'removed'], array_column(SearchOwnerRevisionDecision::cases(), 'value'));
    }

    #[Test]
    public function mapper_round_trips_a_canonical_checksum(): void
    {
        $mapper = new SearchOwnerSourceMapper;
        $row = $mapper->toRow(self::state());
        $restored = $mapper->toState($row);

        self::assertSame(64, strlen($row['revision_checksum']));
        self::assertSame($row['revision_checksum'], $mapper->checksum($row));
        self::assertSame($row, $mapper->toRow($restored));
        self::assertSame('UTC', $restored->effectiveAt->getTimezone()->getName());
    }

    #[Test]
    public function state_rejects_non_positive_or_inconsistent_revisions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SearchOwnerRevisionState(self::document(), 0, SearchOwnerRevisionDecision::Visible, SearchDecisionFixture::make(), new DateTimeImmutable('2026-08-01T08:00:00Z'), new DateTimeImmutable('2026-08-01T08:00:01Z'));
    }

    public static function state(int $revision = 1, int $rank = 500, string $effective = '08:00:00'): SearchOwnerRevisionState
    {
        return new SearchOwnerRevisionState(
            self::document(),
            $revision,
            SearchOwnerRevisionDecision::Visible,
            SearchDecisionFixture::make($revision, $rank),
            new DateTimeImmutable('2026-08-01T'.$effective.'.000000Z'),
            new DateTimeImmutable('2026-08-01T'.$effective.'.100000Z'),
        );
    }

    public static function document(): SearchDocumentId
    {
        return SearchDocumentId::fromString('99100000-0000-4000-8000-000000000001');
    }
}
