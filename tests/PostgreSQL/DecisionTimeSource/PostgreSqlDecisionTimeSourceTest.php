<?php

namespace Tests\PostgreSQL\DecisionTimeSource;

use App\Application\DecisionTimeSource\DecisionTimeReadStatus;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifest;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifestEntry;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationTransition;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use App\Application\PublicProjectionStore\PublicProjectionWriteResult;
use App\Infrastructure\DecisionTimeSource\ContentSeoSnapshotDecisionTimeMapper;
use App\Infrastructure\DecisionTimeSource\ContentSeoSnapshotDecisionTimeReader;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionMapper;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionReader;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionWriter;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicProjectionGenerationManager;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicProjectionGenerationValidator;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotWriteResult;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\ContentSeoSourceSnapshotMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlContentSeoSourceSnapshotReader;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlContentSeoSourceSnapshotWriter;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\ContentSeoSourceSnapshotFixture;
use Tests\Support\PublicListingReadModelFixture;

final class PostgreSqlDecisionTimeSourceTest extends TestCase
{
    private const string ACTIVE = '99100000-0000-4000-8000-000000000001';

    private const string CANDIDATE = '99100000-0000-4000-8000-000000000002';

    private const string LISTING = '99400000-0000-4000-8000-000000000001';

    private PDO $connection;

    private ContentSeoSourceSnapshotMapper $snapshotMapper;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->snapshotMapper = new ContentSeoSourceSnapshotMapper;
    }

    public function test_missing_found_and_corrupted_are_explicit(): void
    {
        $snapshot = ContentSeoSourceSnapshotFixture::make();
        self::assertSame(DecisionTimeReadStatus::Missing, $this->reader()->readByListing($snapshot->listingId->value)->status);
        $this->snapshotWriter()->store($snapshot);
        $found = $this->reader()->readByListing($snapshot->listingId->value);
        self::assertSame(DecisionTimeReadStatus::Found, $found->status);
        $this->assertDecisionTimeSame($snapshot->decisionAt, $found->decisionAt);

        $this->connection->exec("UPDATE content_seo.public_source_snapshots SET payload_checksum='".str_repeat('0', 64)."'");
        self::assertSame(DecisionTimeReadStatus::Corrupted, $this->reader()->readByListing($snapshot->listingId->value)->status);
    }

    public function test_replay_and_external_rollback_preserve_original_decision_time(): void
    {
        $snapshot = ContentSeoSourceSnapshotFixture::make();
        self::assertSame(ContentSeoSnapshotWriteResult::Applied, $this->snapshotWriter()->store($snapshot));
        $expected = $this->reader()->readByListing($snapshot->listingId->value)->decisionAt;
        self::assertSame(ContentSeoSnapshotWriteResult::AlreadyApplied, $this->snapshotWriter()->store($snapshot));
        $this->assertDecisionTimeSame($expected, $this->reader()->readByListing($snapshot->listingId->value)->decisionAt);

        $this->connection->beginTransaction();
        self::assertSame(ContentSeoSnapshotWriteResult::Applied, $this->snapshotWriter()->store(ContentSeoSourceSnapshotFixture::make(2)));
        $this->connection->rollBack();
        $this->assertDecisionTimeSame($expected, $this->reader()->readByListing($snapshot->listingId->value)->decisionAt);
    }

    public function test_projection_rebuild_activation_and_rollback_do_not_change_decision_time(): void
    {
        $snapshot = ContentSeoSourceSnapshotFixture::make();
        $this->snapshotWriter()->store($snapshot);
        $expected = $this->reader()->readByListing($snapshot->listingId->value)->decisionAt;
        $active = PublicProjectionGenerationId::fromString(self::ACTIVE);
        $candidate = PublicProjectionGenerationId::fromString(self::CANDIDATE);
        $this->connection->exec("INSERT INTO public_projection.generations(generation_id,state) VALUES ('".self::ACTIVE."','active')");
        $projectionMapper = new PostgreSqlPublicListingProjectionMapper;
        $validator = new PostgreSqlPublicProjectionGenerationValidator($this->connection, $projectionMapper);
        $manager = new PostgreSqlPublicProjectionGenerationManager($this->connection, $validator);
        self::assertSame(PublicProjectionGenerationTransition::Applied, $manager->createCandidate($candidate));

        $watermark = new PublicProjectionWatermark(1, 1, 1, 1, 1, 1, 1);
        $model = PublicListingReadModelFixture::make(listingId: self::LISTING, canonicalUrl: 'https://appart.sn/annonces/decision-time');
        $projectionReader = new PostgreSqlPublicListingProjectionReader($this->connection, $projectionMapper);
        $projectionWriter = new PostgreSqlPublicListingProjectionWriter($this->connection, $projectionMapper, $projectionReader);
        self::assertSame(PublicProjectionWriteResult::Applied, $projectionWriter->writeCandidate(PublicListingProjectionRecord::current(self::LISTING, 'annonces/decision-time', $model, $watermark, $candidate)));
        $manifest = new PublicProjectionGenerationManifest([new PublicProjectionGenerationManifestEntry(self::LISTING, $watermark)]);
        self::assertSame(PublicProjectionGenerationTransition::Applied, $manager->activate($candidate, $manifest));
        $this->assertDecisionTimeSame($expected, $this->reader()->readByListing(self::LISTING)->decisionAt);
        self::assertSame(PublicProjectionGenerationTransition::Applied, $manager->rollback($active));
        $this->assertDecisionTimeSame($expected, $this->reader()->readByListing(self::LISTING)->decisionAt);
    }

    private function reader(): ContentSeoSnapshotDecisionTimeReader
    {
        return new ContentSeoSnapshotDecisionTimeReader(
            new PostgreSqlContentSeoSourceSnapshotReader($this->connection, $this->snapshotMapper),
            new ContentSeoSnapshotDecisionTimeMapper,
        );
    }

    private function snapshotWriter(): PostgreSqlContentSeoSourceSnapshotWriter
    {
        return new PostgreSqlContentSeoSourceSnapshotWriter($this->connection, $this->snapshotMapper);
    }

    private function assertDecisionTimeSame(?\DateTimeImmutable $expected, ?\DateTimeImmutable $actual): void
    {
        self::assertNotNull($expected);
        self::assertNotNull($actual);
        self::assertSame($expected->format('Y-m-d\TH:i:s.uP'), $actual->format('Y-m-d\TH:i:s.uP'));
    }
}
