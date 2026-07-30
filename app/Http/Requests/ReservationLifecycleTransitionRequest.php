<?php

namespace App\Http\Requests;

use App\Application\ReservationLifecycleEventIntegration\ReservationLifecycleAtomicEventRequest;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ReservationLifecycleTransitionRequest extends FormRequest
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
                static fn (ReservationLifecycleAction $action): string => $action->value,
                array_filter(ReservationLifecycleAction::cases(), static fn (ReservationLifecycleAction $action): bool => $action !== ReservationLifecycleAction::Unknown),
            ))],
            'expectedVersion' => ['bail', 'required', 'integer', 'min:1'],
            'occurredAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/'],
            'recordedAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/', 'after_or_equal:occurredAt'],
        ];
    }

    public function applicationRequest(): ReservationLifecycleAtomicEventRequest
    {
        /** @var array{action:string,expectedVersion:int,occurredAt:string,recordedAt:string} $input */
        $input = $this->validated();

        return new ReservationLifecycleAtomicEventRequest(
            ReservationId::fromString((string) $this->route('reservationId')),
            ReservationLifecycleAction::from($input['action']),
            $input['expectedVersion'],
            new DateTimeImmutable($input['occurredAt']),
            new DateTimeImmutable($input['recordedAt']),
        );
    }
}
