<?php

namespace App\Http\Requests;

use App\Application\ProfessionalStatusEventIntegration\ProfessionalStatusAtomicEventRequest;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusExpectedVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusTransitionContext;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ProfessionalStatusTransitionRequest extends FormRequest
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
                ProfessionalStatusAction::Suspend->value,
                ProfessionalStatusAction::Reactivate->value,
            ])],
            'expectedVersion' => ['bail', 'required', 'integer', 'min:1'],
            'actorId' => ['bail', 'required', 'uuid'],
            'occurredAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/'],
            'recordedAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/', 'after_or_equal:occurredAt'],
        ];
    }

    public function applicationRequest(): ProfessionalStatusAtomicEventRequest
    {
        /** @var array{action:string,expectedVersion:int,actorId:string,occurredAt:string,recordedAt:string} $input */
        $input = $this->validated();

        return new ProfessionalStatusAtomicEventRequest(
            ProfessionalStatusId::fromString((string) $this->route('professionalId')),
            ProfessionalStatusAction::from($input['action']),
            new ProfessionalStatusTransitionContext(
                ProfessionalStatusActorId::fromString($input['actorId']),
                ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable($input['occurredAt'])),
                new ProfessionalStatusExpectedVersion($input['expectedVersion']),
            ),
            ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable($input['recordedAt'])),
        );
    }
}
