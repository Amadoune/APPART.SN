<?php

namespace App\Http\Requests;

use App\Application\MediaItemLifecycleEventIntegration\MediaItemLifecycleAtomicEventRequest;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionDecisionVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionTransitionDecision;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleExpectedVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleTransitionContext;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class MediaItemLifecycleTransitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'action' => ['bail', 'required', 'string', Rule::in([
                MediaItemLifecycleAction::Remove->value,
                MediaItemLifecycleAction::Archive->value,
            ])],
            'contextVersion' => ['bail', 'required', 'integer', Rule::in([MediaItemLifecycleContextVersion::V1->value])],
            'collectionId' => ['bail', 'required', 'uuid'],
            'expectedVersion' => ['bail', 'required', 'integer', 'min:1'],
            'collectionVersion' => ['bail', 'required', 'integer', 'min:1'],
            'actorId' => ['bail', 'required', 'uuid'],
            'occurredAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/'],
            'recordedAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/', 'after_or_equal:occurredAt'],
            'collectionDecision' => ['bail', 'required', 'string', Rule::in(['not_primary', 'replacement_selected'])],
            'replacementMediaId' => ['bail', 'nullable', 'uuid', 'required_if:collectionDecision,replacement_selected', 'prohibited_if:collectionDecision,not_primary', Rule::notIn([(string) $this->route('mediaId')])],
        ];
    }

    public function applicationRequest(): MediaItemLifecycleAtomicEventRequest
    {
        /** @var array{action:string,contextVersion:int,collectionId:string,expectedVersion:int,collectionVersion:int,actorId:string,occurredAt:string,recordedAt:string,collectionDecision:string,replacementMediaId?:string|null} $input */
        $input = $this->validated();
        $mediaId = MediaItemLifecycleId::fromString((string) $this->route('mediaId'));
        $domainMediaId = MediaId::fromString($mediaId->value);
        $decision = $input['collectionDecision'] === 'not_primary'
            ? MediaCollectionTransitionDecision::notPrimary()
            : MediaCollectionTransitionDecision::replacementSelected($domainMediaId, MediaId::fromString((string) $input['replacementMediaId']));

        return new MediaItemLifecycleAtomicEventRequest(
            $mediaId,
            MediaItemLifecycleAction::from($input['action']),
            new MediaItemLifecycleTransitionContext(
                MediaItemLifecycleContextVersion::from($input['contextVersion']),
                MediaCollectionId::fromString($input['collectionId']),
                $domainMediaId,
                new MediaItemLifecycleExpectedVersion($input['expectedVersion']),
                new MediaCollectionDecisionVersion($input['collectionVersion']),
                MediaItemLifecycleActorId::fromString($input['actorId']),
                MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable($input['occurredAt'])),
                $decision,
            ),
            MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable($input['recordedAt'])),
        );
    }
}
