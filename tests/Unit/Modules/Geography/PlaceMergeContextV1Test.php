<?php

namespace Tests\Unit\Modules\Geography;

use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeActorId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextInspection;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextInspectionResult;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextInspectionStatus;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextV1;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeExpectedSourceVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeIntentId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedState;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedTargetVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeOccurredAt;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeReplayOutcome;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PlaceMergeContextV1Test extends TestCase
{
    public function test_context_is_explicit_immutable_and_v1(): void
    {
        $context = $this->context();

        self::assertSame(PlaceMergeContextVersion::V1, $context->contractVersion);
        self::assertSame('10000000-0000-4000-8000-000000000001', $context->sourceId->value);
        self::assertSame('10000000-0000-4000-8000-000000000002', $context->targetId->value);
        self::assertSame(7, $context->expectedSourceVersion->value);
        self::assertSame(11, $context->observedTargetVersion->value);
        self::assertSame(PlaceMergeObservedState::Enabled, $context->observedTargetState);
        self::assertSame(PlaceType::City, $context->observedSourceType);
        self::assertSame(PlaceType::City, $context->observedTargetType);
        self::assertSame('SN', $context->observedSourceCountry->value);
        self::assertSame('SN', $context->observedTargetCountry->value);
        self::assertSame('20000000-0000-4000-8000-000000000001', $context->actor->value);
        self::assertSame('2026-07-25T10:11:12.123456Z', $context->occurredAt->canonical());
        self::assertSame('30000000-0000-4000-8000-000000000001', $context->intentId->value);
        self::assertTrue((new ReflectionClass($context))->isReadOnly());
    }

    #[DataProvider('businessEvidenceProvider')]
    public function test_context_preserves_business_evidence_for_future_closed_decisions(
        PlaceId $source,
        PlaceId $target,
        PlaceMergeObservedState $state,
        PlaceType $sourceType,
        PlaceType $targetType,
        CountryCode $sourceCountry,
        CountryCode $targetCountry,
    ): void {
        $context = $this->context($source, $target, $state, $sourceType, $targetType, $sourceCountry, $targetCountry);

        self::assertSame($source, $context->sourceId);
        self::assertSame($target, $context->targetId);
        self::assertSame($state, $context->observedTargetState);
        self::assertSame($sourceType, $context->observedSourceType);
        self::assertSame($targetType, $context->observedTargetType);
        self::assertSame($sourceCountry, $context->observedSourceCountry);
        self::assertSame($targetCountry, $context->observedTargetCountry);
    }

    /** @return iterable<string, array{PlaceId, PlaceId, PlaceMergeObservedState, PlaceType, PlaceType, CountryCode, CountryCode}> */
    public static function businessEvidenceProvider(): iterable
    {
        $source = PlaceId::fromString('10000000-0000-4000-8000-000000000001');
        $target = PlaceId::fromString('10000000-0000-4000-8000-000000000002');
        $sn = CountryCode::fromString('SN');

        yield 'same identity remains decidable' => [$source, $source, PlaceMergeObservedState::Enabled, PlaceType::City, PlaceType::City, $sn, $sn];
        yield 'disabled target remains decidable' => [$source, $target, PlaceMergeObservedState::Disabled, PlaceType::City, PlaceType::City, $sn, $sn];
        yield 'merged target remains decidable' => [$source, $target, PlaceMergeObservedState::Merged, PlaceType::City, PlaceType::City, $sn, $sn];
        yield 'different type remains decidable' => [$source, $target, PlaceMergeObservedState::Enabled, PlaceType::City, PlaceType::Region, $sn, $sn];
        yield 'different country remains decidable' => [$source, $target, PlaceMergeObservedState::Enabled, PlaceType::City, PlaceType::City, $sn, CountryCode::fromString('FR')];
    }

    public function test_versions_actor_intent_and_instant_reject_implicit_or_invalid_values(): void
    {
        foreach ([
            static fn () => new PlaceMergeExpectedSourceVersion(0),
            static fn () => new PlaceMergeObservedTargetVersion(0),
            static fn () => PlaceMergeActorId::fromString('actor'),
            static fn () => PlaceMergeIntentId::fromString('intent'),
            static fn () => PlaceMergeOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-25T10:00:00+02:00')),
        ] as $invalid) {
            try {
                $invalid();
                self::fail('An invalid explicit context value was accepted.');
            } catch (InvalidArgumentException) {
                self::addToAssertionCount(1);
            }
        }
    }

    public function test_inspection_results_are_closed_and_keep_identity(): void
    {
        $context = $this->context();
        $inspection = new PlaceMergeContextInspection($context, 8);

        $found = PlaceMergeContextInspectionResult::found($inspection);
        $missing = PlaceMergeContextInspectionResult::missing($context->sourceId, $context->intentId);
        $corrupted = PlaceMergeContextInspectionResult::corrupted($context->sourceId, $context->intentId);

        self::assertSame(PlaceMergeContextInspectionStatus::Found, $found->status);
        self::assertSame($inspection, $found->inspection);
        self::assertSame(PlaceMergeContextInspectionStatus::Missing, $missing->status);
        self::assertNull($missing->inspection);
        self::assertSame(PlaceMergeContextInspectionStatus::Corrupted, $corrupted->status);
        self::assertNull($corrupted->inspection);
        self::assertSame($context->sourceId, $missing->sourceId);
        self::assertSame($context->intentId, $corrupted->intentId);
    }

    public function test_inspection_requires_the_immediate_resulting_version(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PlaceMergeContextInspection($this->context(), 9);
    }

    public function test_replay_outcomes_are_closed(): void
    {
        self::assertSame(
            ['already_applied', 'context_divergence', 'conflict', 'inspection_missing', 'inspection_corrupted'],
            array_column(PlaceMergeReplayOutcome::cases(), 'value'),
        );
    }

    private function context(
        ?PlaceId $source = null,
        ?PlaceId $target = null,
        PlaceMergeObservedState $state = PlaceMergeObservedState::Enabled,
        PlaceType $sourceType = PlaceType::City,
        PlaceType $targetType = PlaceType::City,
        ?CountryCode $sourceCountry = null,
        ?CountryCode $targetCountry = null,
    ): PlaceMergeContextV1 {
        return new PlaceMergeContextV1(
            sourceId: $source ?? PlaceId::fromString('10000000-0000-4000-8000-000000000001'),
            targetId: $target ?? PlaceId::fromString('10000000-0000-4000-8000-000000000002'),
            expectedSourceVersion: new PlaceMergeExpectedSourceVersion(7),
            observedTargetVersion: new PlaceMergeObservedTargetVersion(11),
            observedTargetState: $state,
            observedSourceType: $sourceType,
            observedTargetType: $targetType,
            observedSourceCountry: $sourceCountry ?? CountryCode::fromString('SN'),
            observedTargetCountry: $targetCountry ?? CountryCode::fromString('SN'),
            actor: PlaceMergeActorId::fromString('20000000-0000-4000-8000-000000000001'),
            occurredAt: PlaceMergeOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-25T10:11:12.123456+00:00')),
            intentId: PlaceMergeIntentId::fromString('30000000-0000-4000-8000-000000000001'),
        );
    }
}
