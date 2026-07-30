<?php

namespace Tests\Unit\Modules\Geography;

use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleAction;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleTransition;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventCatalog;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventId;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventPayloadV1;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventPayloadVersion;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventType;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventV1;
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
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlaceLifecycleEventContractTest extends TestCase
{
    #[DataProvider('certifiedTransitions')]
    public function test_every_certified_transition_produces_exactly_one_minimal_v1_fact(
        PlaceLifecycleTransition $transition,
        PlaceLifecycleEventType $expectedType,
    ): void {
        $event = (new PlaceLifecycleEventCatalog)->eventFor($transition, self::context(), 8);

        self::assertSame($expectedType, $event->type);
        self::assertSame(PlaceLifecycleEventPayloadVersion::V1, $event->payload->version);
        self::assertSame($transition->from, $event->payload->previousState);
        self::assertSame($transition->action, $event->payload->action);
        self::assertSame($transition->to, $event->payload->currentState);
        self::assertSame(
            ['eventId', 'eventType', 'payloadVersion', 'occurredAt', 'payload'],
            array_keys($event->contract()),
        );

        $expectedPayloadKeys = ['placeId', 'previousState', 'action', 'currentState', 'occurredVersion'];
        if ($transition->action === PlaceLifecycleAction::Merge) {
            $expectedPayloadKeys[] = 'targetPlaceId';
        }

        self::assertSame($expectedPayloadKeys, array_keys($event->payload->fields()));
    }

    public function test_event_identity_is_deterministic_and_action_sensitive(): void
    {
        $catalog = new PlaceLifecycleEventCatalog;
        $context = self::context();
        [$merge] = self::certifiedTransitions()[2];
        [$disable] = self::certifiedTransitions()[1];

        $first = $catalog->eventFor($merge, $context, 8);
        $same = $catalog->eventFor($merge, $context, 8);
        $differentAction = $catalog->eventFor($disable, $context, 8);

        self::assertSame($first->eventId->value, $same->eventId->value);
        self::assertNotSame($first->eventId->value, $differentAction->eventId->value);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first->eventId->value);
    }

    public function test_merge_identity_and_payload_include_the_target_but_other_facts_do_not(): void
    {
        $catalog = new PlaceLifecycleEventCatalog;
        $firstContext = self::context();
        $secondContext = self::context(
            targetId: PlaceId::fromString('10000000-0000-4000-8000-000000000003'),
        );
        [$merge] = self::certifiedTransitions()[2];
        [$disable] = self::certifiedTransitions()[1];

        $firstMerge = $catalog->eventFor($merge, $firstContext, 8);
        $secondMerge = $catalog->eventFor($merge, $secondContext, 8);
        $disabled = $catalog->eventFor($disable, $firstContext, 8);

        self::assertNotSame($firstMerge->eventId->value, $secondMerge->eventId->value);
        self::assertSame($firstContext->targetId->value, $firstMerge->payload->fields()['targetPlaceId']);
        self::assertArrayNotHasKey('targetPlaceId', $disabled->payload->fields());
    }

    public function test_contract_excludes_governance_and_observed_evidence(): void
    {
        [$merge] = self::certifiedTransitions()[2];
        $contract = json_encode(
            (new PlaceLifecycleEventCatalog)->eventFor($merge, self::context(), 8)->contract(),
            JSON_THROW_ON_ERROR,
        );

        foreach ([
            'actor',
            'intent',
            'observedTargetVersion',
            'observedTargetState',
            'observedSourceType',
            'observedTargetType',
            'observedSourceCountry',
            'observedTargetCountry',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contract);
        }
    }

    public function test_uncertified_transition_is_rejected(): void
    {
        $this->expectException(DomainException::class);

        (new PlaceLifecycleEventCatalog)->typeFor(
            new PlaceLifecycleTransition(
                PlaceLifecycleState::Merged,
                PlaceLifecycleAction::Enable,
                PlaceLifecycleState::Enabled,
            ),
        );
    }

    public function test_resulting_version_must_follow_the_certified_context_version(): void
    {
        [$merge] = self::certifiedTransitions()[2];
        $this->expectException(InvalidArgumentException::class);

        (new PlaceLifecycleEventCatalog)->eventFor($merge, self::context(), 9);
    }

    public function test_payload_rejects_a_target_on_a_non_merge_fact(): void
    {
        [$disable, $type] = self::certifiedTransitions()[1];
        $context = self::context();
        $id = PlaceLifecycleEventId::derive(
            $type,
            PlaceLifecycleEventPayloadVersion::V1,
            $context->sourceId,
            $disable,
            8,
            $context->targetId,
        );

        $this->expectException(InvalidArgumentException::class);

        new PlaceLifecycleEventPayloadV1(
            $id,
            $context->sourceId,
            $disable->from,
            $disable->action,
            $disable->to,
            8,
            $context->targetId,
        );
    }

    public function test_event_envelope_rejects_an_identity_owned_by_another_action(): void
    {
        $context = self::context();
        [$enable, $enableType] = self::certifiedTransitions()[0];
        [$disable, $disableType] = self::certifiedTransitions()[1];
        $disableId = PlaceLifecycleEventId::derive(
            $disableType,
            PlaceLifecycleEventPayloadVersion::V1,
            $context->sourceId,
            $disable,
            8,
            null,
        );
        $payload = new PlaceLifecycleEventPayloadV1(
            $disableId,
            $context->sourceId,
            $disable->from,
            $disable->action,
            $disable->to,
            8,
            null,
        );

        $this->expectException(InvalidArgumentException::class);

        new PlaceLifecycleEventV1($enableType, $disableId, $context->occurredAt, $payload);
    }

    public function test_event_types_and_payload_versions_are_closed(): void
    {
        self::assertSame(
            ['place.lifecycle.enabled', 'place.lifecycle.disabled', 'place.lifecycle.merged'],
            array_column(PlaceLifecycleEventType::cases(), 'value'),
        );
        self::assertSame([1], array_column(PlaceLifecycleEventPayloadVersion::cases(), 'value'));
    }

    /** @return list<array{PlaceLifecycleTransition, PlaceLifecycleEventType}> */
    public static function certifiedTransitions(): array
    {
        return [
            [
                new PlaceLifecycleTransition(
                    PlaceLifecycleState::Disabled,
                    PlaceLifecycleAction::Enable,
                    PlaceLifecycleState::Enabled,
                ),
                PlaceLifecycleEventType::Enabled,
            ],
            [
                new PlaceLifecycleTransition(
                    PlaceLifecycleState::Enabled,
                    PlaceLifecycleAction::Disable,
                    PlaceLifecycleState::Disabled,
                ),
                PlaceLifecycleEventType::Disabled,
            ],
            [
                new PlaceLifecycleTransition(
                    PlaceLifecycleState::Enabled,
                    PlaceLifecycleAction::Merge,
                    PlaceLifecycleState::Merged,
                ),
                PlaceLifecycleEventType::Merged,
            ],
            [
                new PlaceLifecycleTransition(
                    PlaceLifecycleState::Disabled,
                    PlaceLifecycleAction::Merge,
                    PlaceLifecycleState::Merged,
                ),
                PlaceLifecycleEventType::Merged,
            ],
        ];
    }

    private static function context(?PlaceId $targetId = null): PlaceMergeContextV1
    {
        return new PlaceMergeContextV1(
            sourceId: PlaceId::fromString('10000000-0000-4000-8000-000000000001'),
            targetId: $targetId ?? PlaceId::fromString('10000000-0000-4000-8000-000000000002'),
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
}
