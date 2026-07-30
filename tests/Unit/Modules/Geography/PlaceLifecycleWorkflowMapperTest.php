<?php

namespace Tests\Unit\Modules\Geography;

use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleAction;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleTransition;
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
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PlaceLifecycleWorkflowMapperTest extends TestCase
{
    public function test_enrollment_mapping_is_mechanical_and_round_trips(): void
    {
        $mapper = new PlaceLifecycleWorkflowMapper;
        $row = $mapper->initial($this->source(), PlaceLifecycleState::Enabled, 7);
        $database = $row;
        $database['entry_checksum'] = $database['checksum'];

        $snapshot = $mapper->snapshot($database);

        self::assertSame($this->source()->value, $row['place_id']);
        self::assertSame(7, $snapshot->version);
        self::assertSame(PlaceLifecycleState::Enabled, $snapshot->state);
        self::assertSame('enrollment', $row['entry_kind']);
        self::assertNull($row['target_id']);
    }

    public function test_transition_mapping_preserves_the_complete_certified_context(): void
    {
        $mapper = new PlaceLifecycleWorkflowMapper;
        $row = $mapper->transition(
            new PlaceLifecycleTransition(PlaceLifecycleState::Enabled, PlaceLifecycleAction::Merge, PlaceLifecycleState::Merged),
            $this->context(),
        );

        self::assertSame(8, $row['version']);
        self::assertSame('transition', $row['entry_kind']);
        self::assertSame('enabled', $row['previous_state']);
        self::assertSame('merged', $row['current_state']);
        self::assertSame('merge', $row['action']);
        self::assertSame($this->target()->value, $row['target_id']);
        self::assertSame(11, $row['target_version']);
        self::assertSame('enabled', $row['target_state']);
        self::assertSame('city', $row['source_type']);
        self::assertSame('city', $row['target_type']);
        self::assertSame('SN', $row['source_country']);
        self::assertSame('SN', $row['target_country']);
        self::assertSame('2026-07-25T10:11:12.123456Z', $row['occurred_at']);
        self::assertSame(1, $row['context_version']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', (string) $row['checksum']);
    }

    public function test_corrupted_row_is_rejected(): void
    {
        $mapper = new PlaceLifecycleWorkflowMapper;
        $row = $mapper->initial($this->source(), PlaceLifecycleState::Enabled, 7);
        $row['entry_checksum'] = str_repeat('0', 64);

        $this->expectException(RuntimeException::class);
        $mapper->snapshot($row);
    }

    private function context(): PlaceMergeContextV1
    {
        return new PlaceMergeContextV1(
            sourceId: $this->source(),
            targetId: $this->target(),
            expectedSourceVersion: new PlaceMergeExpectedSourceVersion(7),
            observedTargetVersion: new PlaceMergeObservedTargetVersion(11),
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
