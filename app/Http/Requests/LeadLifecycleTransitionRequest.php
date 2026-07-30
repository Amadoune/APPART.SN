<?php

namespace App\Http\Requests;

use App\Application\LeadLifecycleEventIntegration\LeadLifecycleAtomicEventRequest;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class LeadLifecycleTransitionRequest extends FormRequest
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
                static fn (LeadLifecycleAction $action): string => $action->value,
                array_filter(LeadLifecycleAction::cases(), static fn (LeadLifecycleAction $action): bool => $action !== LeadLifecycleAction::Unknown),
            ))],
            'expectedVersion' => ['bail', 'required', 'integer', 'min:1'],
            'actorId' => ['bail', 'required', 'uuid'],
            'occurredAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/'],
            'recordedAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/', 'after_or_equal:occurredAt'],
        ];
    }

    public function applicationRequest(): LeadLifecycleAtomicEventRequest
    {
        /** @var array{action:string,expectedVersion:int,actorId:string,occurredAt:string,recordedAt:string} $input */
        $input = $this->validated();

        return new LeadLifecycleAtomicEventRequest(
            LeadId::fromString((string) $this->route('leadId')),
            LeadLifecycleAction::from($input['action']),
            $input['expectedVersion'],
            LeadLifecycleActorId::fromString($input['actorId']),
            LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable($input['occurredAt'])),
            LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable($input['recordedAt'])),
        );
    }
}
