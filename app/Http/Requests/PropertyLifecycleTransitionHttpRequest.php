<?php

namespace App\Http\Requests;

use App\Application\PropertyLifecycleEventIntegration\PropertyLifecycleEventOrchestrationRequest;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventInstant;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleAction;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PropertyLifecycleTransitionHttpRequest extends FormRequest
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
                static fn (PropertyLifecycleAction $action): string => $action->value,
                array_filter(PropertyLifecycleAction::cases(), static fn (PropertyLifecycleAction $action): bool => $action !== PropertyLifecycleAction::Unknown),
            ))],
            'expectedVersion' => ['bail', 'required', 'integer', 'min:1'],
            'occurredAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/'],
            'recordedAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/', 'after_or_equal:occurredAt'],
        ];
    }

    public function applicationRequest(): PropertyLifecycleEventOrchestrationRequest
    {
        /** @var array{action:string,expectedVersion:int,occurredAt:string,recordedAt:string} $input */
        $input = $this->validated();

        return new PropertyLifecycleEventOrchestrationRequest(
            PropertyId::fromString((string) $this->route('propertyId')),
            PropertyLifecycleAction::from($input['action']),
            $input['expectedVersion'],
            PropertyLifecycleEventInstant::fromCanonicalUtc($input['occurredAt']),
            PropertyLifecycleEventInstant::fromCanonicalUtc($input['recordedAt']),
        );
    }
}
