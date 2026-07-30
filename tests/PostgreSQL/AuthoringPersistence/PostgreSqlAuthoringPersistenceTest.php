<?php

namespace Tests\PostgreSQL\AuthoringPersistence;

use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\AuthoringPersistenceWriteResult;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\AuthoringPortfolioItem;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingDraftState;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingOwnershipState;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\AuthoringPortfolioMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingDraftMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingOwnershipMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlAuthoringPortfolioStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingDraftStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingOwnershipStore;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringPersistenceWriteResult;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyAuthoringStore;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyAuthoringMapper;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAuthoringPersistenceTest extends TestCase
{
    private const PROPERTY = '56000000-0000-4000-8000-000000000001';

    private const LISTING = '56000000-0000-4000-8000-000000000002';

    private const OWNER = '56000000-0000-4000-8000-000000000003';

    private const DELEGATE = '56000000-0000-4000-8000-000000000004';

    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    #[Test]
    public function property_authoring_preserves_owner_and_converges_by_intent(): void
    {
        $store = new PostgreSqlPropertyAuthoringStore($this->connection, new PropertyAuthoringMapper);
        $state = new PropertyAuthoringState(self::PROPERTY, self::OWNER, 1, $this->intent(1), $this->checksum('property-1'));

        self::assertSame(PropertyAuthoringPersistenceWriteResult::Applied, $store->save($state, 0));
        self::assertSame(PropertyAuthoringPersistenceWriteResult::AlreadyApplied, $store->save($state, 0));
        self::assertSame(PropertyAuthoringPersistenceWriteResult::DivergentIntent, $store->save(new PropertyAuthoringState(self::PROPERTY, self::OWNER, 2, $this->intent(1), $this->checksum('different')), 1));
        self::assertSame(PropertyAuthoringPersistenceWriteResult::Rejected, $store->save(new PropertyAuthoringState(self::PROPERTY, self::DELEGATE, 2, $this->intent(2), $this->checksum('takeover')), 1));
        self::assertSame(self::OWNER, $store->read(self::PROPERTY)?->ownerAccountId);
    }

    #[Test]
    public function draft_is_optimistically_locked_and_revision_is_append_only(): void
    {
        $store = new PostgreSqlListingDraftStore($this->connection, new ListingDraftMapper);
        $first = $this->draft(1, 1, 'Initial');
        $second = $this->draft(2, 2, 'Updated');

        self::assertSame(AuthoringPersistenceWriteResult::Applied, $store->save($first, 0));
        self::assertSame(AuthoringPersistenceWriteResult::VersionConflict, $store->save($second, 0));
        self::assertSame(AuthoringPersistenceWriteResult::Applied, $store->save($second, 1));
        self::assertSame('Updated', $store->read(self::LISTING)?->title);
        self::assertSame(2, $this->tableCount('listing_authoring.draft_revisions'));
    }

    #[Test]
    public function ownership_and_delegations_commit_as_one_owner(): void
    {
        $store = new PostgreSqlListingOwnershipStore($this->connection, new ListingOwnershipMapper);
        $first = new ListingOwnershipState(self::LISTING, self::PROPERTY, self::OWNER, [self::DELEGATE => ['VIEW', 'EDIT']], 1, $this->intent(1), $this->checksum('ownership-1'));
        $second = new ListingOwnershipState(self::LISTING, self::PROPERTY, self::OWNER, [self::DELEGATE => ['SUBMIT']], 2, $this->intent(2), $this->checksum('ownership-2'));

        self::assertSame(AuthoringPersistenceWriteResult::Applied, $store->save($first, 0));
        self::assertSame(AuthoringPersistenceWriteResult::Applied, $store->save($second, 1));
        self::assertSame(['SUBMIT'], $store->read(self::LISTING)?->delegations[self::DELEGATE]);
        self::assertSame(1, $this->tableCount('listing_authoring.delegations'));
    }

    #[Test]
    public function portfolio_is_monotone_and_reconstructible(): void
    {
        $store = new PostgreSqlAuthoringPortfolioStore($this->connection, new AuthoringPortfolioMapper);
        $current = new AuthoringPortfolioItem(self::OWNER, self::LISTING, self::PROPERTY, 'OWNER', 2, 1, 'COMPLETE', 10);
        $stale = new AuthoringPortfolioItem(self::OWNER, self::LISTING, self::PROPERTY, 'OWNER', 1, 1, 'INCOMPLETE', 9);

        self::assertSame(AuthoringPersistenceWriteResult::Applied, $store->project($current));
        self::assertSame(AuthoringPersistenceWriteResult::AlreadyApplied, $store->project($stale));
        self::assertSame('COMPLETE', $store->listFor(self::OWNER)[0]->completenessCode);
        self::assertSame(10, $store->listFor(self::OWNER)[0]->sourceCheckpoint);
    }

    #[Test]
    public function concurrent_draft_updates_have_one_winner_and_no_partial_revision(): void
    {
        $store = new PostgreSqlListingDraftStore($this->connection, new ListingDraftMapper);
        self::assertSame(AuthoringPersistenceWriteResult::Applied, $store->save($this->draft(1, 1, 'Initial'), 0));

        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'authoring-draft-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $worker],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start authoring persistence worker.');
            }
            $processes[] = [$process, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        if (! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) {
            throw new RuntimeException('Authoring workers did not reach the barrier.');
        }
        touch($barrier.'.start');

        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            self::assertSame('', trim(stream_get_contents($pipes[2])));
            self::assertSame(0, proc_close($process));
        }
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        sort($results);
        self::assertSame(['Applied', 'VersionConflict'], $results);
        self::assertSame(2, $store->read(self::LISTING)?->version);
        self::assertSame(2, $this->tableCount('listing_authoring.draft_revisions'));
    }

    private function draft(int $version, int $intent, string $title): ListingDraftState
    {
        return new ListingDraftState(self::LISTING, self::PROPERTY, $title, 'Description', 'sale', 100000, 'XOF', 0, '2026-08-01', 'platform', $version, $this->intent($intent), $this->checksum('draft-'.$intent));
    }

    private function intent(int $suffix): string
    {
        return sprintf('56000000-0000-4000-8001-%012d', $suffix);
    }

    private function checksum(string $value): string
    {
        return hash('sha256', $value);
    }

    private function tableCount(string $table): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM {$table}")->fetchColumn();
    }
}
