<?php

namespace Appart\Modules\Geography\Infrastructure\Persistence;

use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleTransition;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\PlaceLifecycleStoredState;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeActorId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextInspection;
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
use RuntimeException;
use Throwable;

final readonly class PlaceLifecycleWorkflowMapper
{
    /** @return array<string, int|string|null> */
    public function initial(PlaceId $placeId, PlaceLifecycleState $state, int $version): array
    {
        $row = [
            'place_id' => $placeId->value,
            'version' => $version,
            'entry_kind' => 'enrollment',
            'previous_state' => null,
            'current_state' => $state->value,
            'action' => null,
            'target_id' => null,
            'target_version' => null,
            'target_state' => null,
            'source_type' => null,
            'target_type' => null,
            'source_country' => null,
            'target_country' => null,
            'actor_id' => null,
            'occurred_at' => null,
            'intent_id' => null,
            'context_version' => null,
        ];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @return array<string, int|string|null> */
    public function transition(PlaceLifecycleTransition $transition, PlaceMergeContextV1 $context): array
    {
        $row = [
            'place_id' => $context->sourceId->value,
            'version' => $context->expectedSourceVersion->value + 1,
            'entry_kind' => 'transition',
            'previous_state' => $transition->from->value,
            'current_state' => $transition->to->value,
            'action' => $transition->action->value,
            'target_id' => $context->targetId->value,
            'target_version' => $context->observedTargetVersion->value,
            'target_state' => $context->observedTargetState->value,
            'source_type' => $context->observedSourceType->value,
            'target_type' => $context->observedTargetType->value,
            'source_country' => $context->observedSourceCountry->value,
            'target_country' => $context->observedTargetCountry->value,
            'actor_id' => $context->actor->value,
            'occurred_at' => $context->occurredAt->canonical(),
            'intent_id' => $context->intentId->value,
            'context_version' => $context->contractVersion->value,
        ];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @param array<string, mixed> $row */
    public function snapshot(array $row): PlaceLifecycleStoredState
    {
        try {
            if (! hash_equals((string) $row['entry_checksum'], $this->checksum($row))) {
                throw new RuntimeException('Corrupted place lifecycle entry.');
            }

            return new PlaceLifecycleStoredState(
                PlaceId::fromString((string) $row['place_id']),
                PlaceLifecycleState::from((string) $row['current_state']),
                (int) $row['version'],
            );
        } catch (Throwable $error) {
            throw new RuntimeException('Invalid place lifecycle persistence row.', 0, $error);
        }
    }

    /** @param array<string, mixed> $row */
    public function contextInspection(array $row): PlaceMergeContextInspection
    {
        if (! hash_equals((string) $row['entry_checksum'], $this->checksum($row))
            || (string) $row['entry_kind'] !== 'transition') {
            throw new RuntimeException('Corrupted place lifecycle context entry.');
        }

        $version = (int) $row['version'];
        $context = new PlaceMergeContextV1(
            PlaceId::fromString((string) $row['place_id']),
            PlaceId::fromString((string) $row['target_id']),
            new PlaceMergeExpectedSourceVersion($version - 1),
            new PlaceMergeObservedTargetVersion((int) $row['target_version']),
            PlaceMergeObservedState::from((string) $row['target_state']),
            PlaceType::from((string) $row['source_type']),
            PlaceType::from((string) $row['target_type']),
            CountryCode::fromString((string) $row['source_country']),
            CountryCode::fromString((string) $row['target_country']),
            PlaceMergeActorId::fromString((string) $row['actor_id']),
            PlaceMergeOccurredAt::fromExplicitUtc(new DateTimeImmutable((string) $row['occurred_at'])),
            PlaceMergeIntentId::fromString((string) $row['intent_id']),
        );

        return new PlaceMergeContextInspection($context, $version);
    }

    /** @param array<string, mixed> $row */
    public function checksum(array $row): string
    {
        $values = [];
        foreach ([
            'place_id', 'version', 'entry_kind', 'previous_state',
            'current_state', 'action', 'target_id', 'target_version',
            'target_state', 'source_type', 'target_type', 'source_country',
            'target_country', 'actor_id', 'occurred_at', 'intent_id',
            'context_version',
        ] as $field) {
            $values[] = ($row[$field] ?? null) === null ? '' : (string) $row[$field];
        }

        return hash('sha256', implode("\n", $values));
    }
}
