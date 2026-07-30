<?php

namespace App\Http\Requests;

use App\Application\PublicAuthoringIntegration\PublicAuthoringJourneyOperation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class PublicAuthoringJourneyHttpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->attributes->has('iam_account_id');
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            '_intentId' => ['bail', 'required', 'uuid'],
            'propertyId' => $this->propertyRule(),
            'listingId' => $this->listingRule(),
            'expectedVersion' => ['bail', 'required', 'integer', 'min:0'],
            'requestedAt' => ['bail', 'required', 'date_format:Y-m-d\TH:i:s.u\Z'],
            'revisionId' => $this->operation() === PublicAuthoringJourneyOperation::CreateListing
                ? ['bail', 'required', 'uuid']
                : ['prohibited'],
            'title' => $this->contentRule(['string', 'min:1', 'max:180'], true),
            'description' => $this->contentRule(['string', 'max:20000']),
            'transactionKind' => $this->contentRule([Rule::in(['sale', 'rent'])], true),
            'priceMinor' => $this->contentRule(['integer', 'min:0']),
            'currency' => $this->contentRule([Rule::in(['XOF'])]),
            'chargesMinor' => $this->contentRule(['integer', 'min:0']),
            'availabilityDate' => $this->contentRule(['date_format:Y-m-d']),
            'contactPreference' => $this->contentRule([Rule::in(['platform', 'phone', 'email'])]),
            'delegateAccountId' => in_array($this->operation(), [
                PublicAuthoringJourneyOperation::GrantDelegation,
                PublicAuthoringJourneyOperation::RevokeDelegation,
            ], true) ? ['bail', 'required', 'uuid'] : ['prohibited'],
            'permissions' => in_array($this->operation(), [
                PublicAuthoringJourneyOperation::GrantDelegation,
                PublicAuthoringJourneyOperation::RevokeDelegation,
            ], true) ? ['bail', 'required', 'array', 'min:1'] : ['prohibited'],
            'permissions.*' => ['string', Rule::in(['VIEW', 'EDIT', 'SUBMIT'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['_intentId' => $this->header('Idempotency-Key')]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (array_diff(array_keys($this->all()), array_keys($this->rules())) !== []) {
                $validator->errors()->add('_request', 'Unknown public authoring fields are forbidden.');
            }
        });
    }

    public function operation(): PublicAuthoringJourneyOperation
    {
        return PublicAuthoringJourneyOperation::from((string) $this->route('journeyOperation'));
    }

    /** @return list<mixed> */
    private function propertyRule(): array
    {
        return in_array($this->operation(), [
            PublicAuthoringJourneyOperation::InitiateProperty,
            PublicAuthoringJourneyOperation::UpdateProperty,
            PublicAuthoringJourneyOperation::CreateListing,
        ], true) ? ['bail', 'required', 'uuid'] : ['prohibited'];
    }

    /** @return list<mixed> */
    private function listingRule(): array
    {
        return in_array($this->operation(), [
            PublicAuthoringJourneyOperation::CreateListing,
            PublicAuthoringJourneyOperation::UpdateDraft,
            PublicAuthoringJourneyOperation::GrantDelegation,
            PublicAuthoringJourneyOperation::RevokeDelegation,
            PublicAuthoringJourneyOperation::SubmitListing,
        ], true) ? ['bail', 'required', 'uuid'] : ['prohibited'];
    }

    /** @param list<mixed> $rules
     * @return list<mixed>
     */
    private function contentRule(array $rules, bool $requiredOnCreate = false): array
    {
        return match ($this->operation()) {
            PublicAuthoringJourneyOperation::CreateListing => [
                $requiredOnCreate ? 'required' : 'sometimes',
                ...$rules,
            ],
            PublicAuthoringJourneyOperation::UpdateDraft => ['sometimes', ...$rules],
            default => ['prohibited'],
        };
    }
}
