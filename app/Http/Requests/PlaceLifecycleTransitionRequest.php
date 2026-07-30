<?php

namespace App\Http\Requests;

use App\Application\PlaceLifecycleEventIntegration\PlaceLifecycleAtomicEventRequest;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleAction;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleCurrentState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycleOrchestration\PlaceLifecycleOrchestrationRequest;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeActorId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextV1;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeExpectedSourceVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeIntentId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedState;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedTargetVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeOccurredAt;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class PlaceLifecycleTransitionRequest extends FormRequest
{
    /** @var list<string> */
    private const array FIELDS = [
        'action',
        'currentState',
        'contextVersion',
        'expectedSourceVersion',
        'targetId',
        'observedTargetVersion',
        'observedTargetState',
        'observedSourceType',
        'observedTargetType',
        'observedSourceCountry',
        'observedTargetCountry',
        'actorId',
        'occurredAt',
        'recordedAt',
        'intentId',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'action' => ['bail', 'required', 'string', Rule::in(array_map(
                static fn (PlaceLifecycleAction $action): string => $action->value,
                PlaceLifecycleAction::cases(),
            ))],
            'currentState' => ['bail', 'required', 'string', Rule::in(array_map(
                static fn (PlaceLifecycleState $state): string => $state->value,
                PlaceLifecycleState::cases(),
            ))],
            'contextVersion' => ['bail', 'required', 'integer', Rule::in([
                PlaceMergeContextVersion::V1->value,
            ])],
            'expectedSourceVersion' => ['bail', 'required', 'integer', 'min:1'],
            'targetId' => ['bail', 'required', 'uuid', 'different:actorId', 'different:intentId'],
            'observedTargetVersion' => ['bail', 'required', 'integer', 'min:1'],
            'observedTargetState' => ['bail', 'required', 'string', Rule::in(array_map(
                static fn (PlaceMergeObservedState $state): string => $state->value,
                PlaceMergeObservedState::cases(),
            ))],
            'observedSourceType' => ['bail', 'required', 'string', Rule::in(array_map(
                static fn (PlaceType $type): string => $type->value,
                PlaceType::cases(),
            ))],
            'observedTargetType' => ['bail', 'required', 'string', Rule::in(array_map(
                static fn (PlaceType $type): string => $type->value,
                PlaceType::cases(),
            ))],
            'observedSourceCountry' => ['bail', 'required', 'string', 'regex:/^[A-Z]{2}$/'],
            'observedTargetCountry' => ['bail', 'required', 'string', 'regex:/^[A-Z]{2}$/'],
            'actorId' => ['bail', 'required', 'uuid', 'different:intentId'],
            'occurredAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/'],
            'recordedAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/', 'after_or_equal:occurredAt'],
            'intentId' => ['bail', 'required', 'uuid'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $unknown = array_diff(array_keys($this->all()), self::FIELDS);
            if ($unknown !== []) {
                $validator->errors()->add('_request', 'Unknown Place Lifecycle fields are forbidden.');
            }

            $placeId = (string) $this->route('placeId');
            if ($placeId !== '' && $placeId === (string) $this->input('targetId')) {
                $validator->errors()->add('targetId', 'The source and target identities must differ.');
            }
        });
    }

    public function applicationRequest(): PlaceLifecycleAtomicEventRequest
    {
        /** @var array{action:string,currentState:string,contextVersion:int,expectedSourceVersion:int,targetId:string,observedTargetVersion:int,observedTargetState:string,observedSourceType:string,observedTargetType:string,observedSourceCountry:string,observedTargetCountry:string,actorId:string,occurredAt:string,recordedAt:string,intentId:string} $input */
        $input = $this->validated();
        $sourceId = PlaceId::fromString((string) $this->route('placeId'));
        $context = new PlaceMergeContextV1(
            $sourceId,
            PlaceId::fromString($input['targetId']),
            new PlaceMergeExpectedSourceVersion($input['expectedSourceVersion']),
            new PlaceMergeObservedTargetVersion($input['observedTargetVersion']),
            PlaceMergeObservedState::from($input['observedTargetState']),
            PlaceType::from($input['observedSourceType']),
            PlaceType::from($input['observedTargetType']),
            CountryCode::fromString($input['observedSourceCountry']),
            CountryCode::fromString($input['observedTargetCountry']),
            PlaceMergeActorId::fromString($input['actorId']),
            PlaceMergeOccurredAt::fromExplicitUtc(new DateTimeImmutable($input['occurredAt'])),
            PlaceMergeIntentId::fromString($input['intentId']),
        );

        return new PlaceLifecycleAtomicEventRequest(
            new PlaceLifecycleOrchestrationRequest(
                new PlaceLifecycleCurrentState(
                    $sourceId,
                    PlaceLifecycleState::from($input['currentState']),
                ),
                PlaceLifecycleAction::from($input['action']),
                $context,
            ),
            new DateTimeImmutable($input['recordedAt']),
        );
    }
}
