<?php

namespace App\Http\Requests;

use App\Application\ListingPublicationEventIntegration\ListingPublicationEventOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventInstant;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventMetadata;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListingPublicationTransitionHttpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'action' => ['bail', 'required', 'string', Rule::in(array_map(
                static fn (ListingPublicationAction $action): string => $action->value,
                array_filter(ListingPublicationAction::cases(), static fn (ListingPublicationAction $action): bool => $action !== ListingPublicationAction::Unknown),
            ))],
            'expectedVersion' => ['bail', 'required', 'integer', 'min:1'],
            'occurredAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/'],
            'recordedAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/', 'gte:occurredAt'],
        ];
    }

    public function applicationRequest(): ListingPublicationEventOrchestrationRequest
    {
        /** @var array{action:string,expectedVersion:int,occurredAt:string,recordedAt:string} $input */
        $input = $this->validated();

        return new ListingPublicationEventOrchestrationRequest(
            new ListingPublicationOrchestrationRequest(
                ListingId::fromString((string) $this->route('listingId')),
                ListingPublicationAction::from($input['action']),
                $input['expectedVersion'],
            ),
            new ListingPublicationEventMetadata(
                ListingPublicationEventInstant::fromCanonicalUtc($input['occurredAt']),
                ListingPublicationEventInstant::fromCanonicalUtc($input['recordedAt']),
            ),
        );
    }
}
