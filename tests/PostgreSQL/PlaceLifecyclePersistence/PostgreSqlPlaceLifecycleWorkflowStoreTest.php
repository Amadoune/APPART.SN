<?php

namespace Tests\PostgreSQL\PlaceLifecyclePersistence;

use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleAction;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleTransition;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\PlaceLifecyclePersistenceReadStatus;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\PlaceLifecyclePersistenceWriteResult;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeActorId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextV1;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeExpectedSourceVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeIntentId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedState;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedTargetVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeOccurredAt;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceLifecycleWorkflowMapper;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlPlaceLifecycleWorkflowStore;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPlaceLifecycleWorkflowStoreTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlPlaceLifecycleWorkflowStore $store;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->store = new PostgreSqlPlaceLifecycleWorkflowStore($this->connection, new PlaceLifecycleWorkflowMapper);
    }

    public function test_initialize_append_read_and_idempotence_are_append_only(): void
    {
        self::assertSame(PlaceLifecyclePersistenceWriteResult::Applied, $this->store->initialize($this->source(), PlaceLifecycleState::Enabled, 7));
        self::assertSame(PlaceLifecyclePersistenceWriteResult::AlreadyApplied, $this->store->initialize($this->source(), PlaceLifecycleState::Enabled, 7));
        self::assertSame(PlaceLifecyclePersistenceWriteResult::Applied, $this->store->initialize($this->target(), PlaceLifecycleState::Enabled, 11));

        $transition = $this->mergeTransition();
        $context = $this->context();
        self::assertSame(PlaceLifecyclePersistenceWriteResult::Applied, $this->store->append($transition, $context));
        self::assertSame(PlaceLifecyclePersistenceWriteResult::AlreadyApplied, $this->store->append($transition, $context));

        $read = $this->store->read($this->source());
        self::assertSame(PlaceLifecyclePersistenceReadStatus::Found, $read->status);
        self::assertNotNull($read->snapshot);
        self::assertSame(PlaceLifecycleState::Merged, $read->snapshot->state);
        self::assertSame(8, $read->snapshot->version);
        self::assertSame(2, (int) $this->connection->query("SELECT COUNT(*) FROM geography.place_lifecycle_transitions WHERE place_id='{$this->source()->value}'")->fetchColumn());
    }

    public function test_source_and_target_version_conflicts_are_distinct_and_write_nothing(): void
    {
        $this->store->initialize($this->source(), PlaceLifecycleState::Enabled, 7);
        $this->store->initialize($this->target(), PlaceLifecycleState::Enabled, 12);

        self::assertSame(
            PlaceLifecyclePersistenceWriteResult::TargetVersionConflict,
            $this->store->append($this->mergeTransition(), $this->context()),
        );

        $sourceConflict = $this->context(sourceVersion: 6, targetVersion: 12);
        self::assertSame(
            PlaceLifecyclePersistenceWriteResult::SourceVersionConflict,
            $this->store->append($this->mergeTransition(), $sourceConflict),
        );
        self::assertSame(1, (int) $this->connection->query("SELECT COUNT(*) FROM geography.place_lifecycle_transitions WHERE place_id='{$this->source()->value}'")->fetchColumn());
    }

    public function test_state_conflict_is_closed_and_does_not_append(): void
    {
        $this->store->initialize($this->source(), PlaceLifecycleState::Disabled, 7);
        $this->store->initialize($this->target(), PlaceLifecycleState::Enabled, 11);

        self::assertSame(
            PlaceLifecyclePersistenceWriteResult::StateConflict,
            $this->store->append($this->mergeTransition(), $this->context()),
        );
    }

    public function test_non_merge_transition_does_not_claim_target_concurrency_ownership(): void
    {
        $this->store->initialize($this->source(), PlaceLifecycleState::Enabled, 7);
        $transition = new PlaceLifecycleTransition(
            PlaceLifecycleState::Enabled,
            PlaceLifecycleAction::Disable,
            PlaceLifecycleState::Disabled,
        );

        self::assertSame(
            PlaceLifecyclePersistenceWriteResult::Applied,
            $this->store->append($transition, $this->context()),
        );
        $read = $this->store->read($this->source());
        self::assertNotNull($read->snapshot);
        self::assertSame(PlaceLifecycleState::Disabled, $read->snapshot->state);
    }

    public function test_external_transaction_rollback_removes_the_complete_append(): void
    {
        $this->store->initialize($this->source(), PlaceLifecycleState::Enabled, 7);
        $this->store->initialize($this->target(), PlaceLifecycleState::Enabled, 11);

        $this->connection->beginTransaction();
        self::assertSame(PlaceLifecyclePersistenceWriteResult::Applied, $this->store->append($this->mergeTransition(), $this->context()));
        $this->connection->rollBack();

        $read = $this->store->read($this->source());
        self::assertNotNull($read->snapshot);
        self::assertSame(PlaceLifecycleState::Enabled, $read->snapshot->state);
        self::assertSame(7, $read->snapshot->version);
    }

    public function test_missing_and_corrupted_reads_are_closed(): void
    {
        self::assertSame(PlaceLifecyclePersistenceReadStatus::Missing, $this->store->read($this->source())->status);

        $this->store->initialize($this->source(), PlaceLifecycleState::Enabled, 7);
        $this->connection->exec("UPDATE geography.place_lifecycle_transitions SET entry_checksum='".str_repeat('0', 64)."' WHERE place_id='{$this->source()->value}'");

        self::assertSame(PlaceLifecyclePersistenceReadStatus::Corrupted, $this->store->read($this->source())->status);
    }

    public function test_concurrent_identical_merges_lock_source_and_target_and_converge(): void
    {
        $this->store->initialize($this->source(), PlaceLifecycleState::Enabled, 7);
        $this->store->initialize($this->target(), PlaceLifecycleState::Enabled, 11);
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'place-lifecycle-'.bin2hex(random_bytes(8));
        $processes = [];

        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start place lifecycle worker.');
            }
            $processes[] = [$process, $pipes];
        }

        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        touch($barrier.'.start');
        $results = [];

        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            $error = trim(stream_get_contents($pipes[2]));
            if (proc_close($process) !== 0 || $error !== '') {
                throw new RuntimeException($error);
            }
        }

        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(2, (int) $this->connection->query("SELECT COUNT(*) FROM geography.place_lifecycle_transitions WHERE place_id='{$this->source()->value}'")->fetchColumn());
    }

    private function mergeTransition(): PlaceLifecycleTransition
    {
        return new PlaceLifecycleTransition(
            PlaceLifecycleState::Enabled,
            PlaceLifecycleAction::Merge,
            PlaceLifecycleState::Merged,
        );
    }

    private function context(int $sourceVersion = 7, int $targetVersion = 11): PlaceMergeContextV1
    {
        return new PlaceMergeContextV1(
            sourceId: $this->source(),
            targetId: $this->target(),
            expectedSourceVersion: new PlaceMergeExpectedSourceVersion($sourceVersion),
            observedTargetVersion: new PlaceMergeObservedTargetVersion($targetVersion),
            observedTargetState: PlaceMergeObservedState::Enabled,
            observedSourceType: PlaceType::City,
            observedTargetType: PlaceType::City,
            observedSourceCountry: CountryCode::fromString('SN'),
            observedTargetCountry: CountryCode::fromString('SN'),
            actor: PlaceMergeActorId::fromString('20000000-0000-4000-8000-000000000001'),
            occurredAt: PlaceMergeOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-25T10:11:12.123456+00:00')),
            intentId: PlaceMergeIntentId::fromString('30000000-0000-4000-8000-000000000001'),
        );
    }

    private function source(): PlaceId
    {
        return PlaceId::fromString('10000000-0000-4000-8000-000000000001');
    }

    private function target(): PlaceId
    {
        return PlaceId::fromString('10000000-0000-4000-8000-000000000002');
    }
}
