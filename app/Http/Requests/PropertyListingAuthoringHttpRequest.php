<?php

namespace App\Http\Requests;

use App\Application\PropertyListingAuthoringHttp\PropertyListingAuthoringHttpOperation;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class PropertyListingAuthoringHttpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->attributes->has('iam_account_id');
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $common = ['_intentId' => $this->operation()->isRead() ? ['nullable'] : ['bail', 'required', 'uuid']];

        return $common + match ($this->operation()) {
            PropertyListingAuthoringHttpOperation::InitiateProperty => $this->propertyFields(),
            PropertyListingAuthoringHttpOperation::PatchProperty => [
                'expectedVersion' => ['bail', 'required', 'integer', 'min:1'],
                ...$this->propertyFields(),
            ],
            PropertyListingAuthoringHttpOperation::CreateListing => [
                'listingId' => ['bail', 'required', 'uuid'],
                'title' => ['bail', 'required', 'string', 'min:1', 'max:180'],
                'transactionKind' => ['bail', 'required', Rule::in(['sale', 'rent'])],
            ],
            PropertyListingAuthoringHttpOperation::PatchDraft => [
                'expectedVersion' => ['bail', 'required', 'integer', 'min:1'],
                'title' => ['sometimes', 'string', 'min:1', 'max:180'],
                'description' => ['sometimes', 'string', 'max:20000'],
                'transactionKind' => ['sometimes', Rule::in(['sale', 'rent'])],
                'priceMinor' => ['sometimes', 'integer', 'min:0'],
                'currency' => ['sometimes', Rule::in(['XOF'])],
                'chargesMinor' => ['sometimes', 'integer', 'min:0'],
                'availabilityDate' => ['sometimes', 'date_format:Y-m-d'],
                'contactPreference' => ['sometimes', Rule::in(['platform', 'phone', 'email'])],
            ],
            PropertyListingAuthoringHttpOperation::GrantDelegation,
            PropertyListingAuthoringHttpOperation::RevokeDelegation => [
                'expectedVersion' => ['bail', 'required', 'integer', 'min:1'],
                'delegateAccountId' => ['bail', 'required', 'uuid'],
                'permissions' => ['bail', 'required', 'array', 'min:1'],
                'permissions.*' => ['string', Rule::in(['VIEW', 'EDIT', 'SUBMIT'])],
            ],
            PropertyListingAuthoringHttpOperation::RequestSubmission => [
                'expectedVersion' => ['bail', 'required', 'integer', 'min:1'],
            ],
            PropertyListingAuthoringHttpOperation::ReadProperty,
            PropertyListingAuthoringHttpOperation::ReadDraft,
            PropertyListingAuthoringHttpOperation::AssessCompleteness,
            PropertyListingAuthoringHttpOperation::Portfolio => [],
        };
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['_intentId' => $this->header('Idempotency-Key')]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowed = array_keys($this->rules());
            if (array_diff(array_keys($this->all()), $allowed) !== []) {
                $validator->errors()->add('_request', 'Unknown authoring fields are forbidden.');
            }
        });
    }

    public function operation(): PropertyListingAuthoringHttpOperation
    {
        return PropertyListingAuthoringHttpOperation::from((string) $this->route('authoring_operation'));
    }

    /** @return array<string, list<mixed>> */
    private function propertyFields(): array
    {
        return [
            'propertyType' => ['sometimes', Rule::in(['apartment', 'house', 'villa', 'land', 'office', 'commercial'])],
            'city' => ['sometimes', 'string', 'min:2', 'max:120'],
            'neighborhood' => ['sometimes', 'string', 'min:2', 'max:120'],
            'propertyReference' => ['sometimes', 'string', 'regex:~^[A-Za-z0-9][A-Za-z0-9._/-]{3,63}$~'],
            'surfaceSquareMeters' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:10000000'],
            'rooms' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:1000'],
            'bathrooms' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:1000'],
            'constructionYear' => ['sometimes', 'nullable', 'integer', 'min:1800', 'max:9999'],
            'addressLine' => ['sometimes', 'nullable', 'string', 'min:3', 'max:255'],
            'geographicPlaceId' => ['sometimes', 'uuid'],
            'geographicPlaceType' => ['required_with:geographicPlaceId', Rule::enum(PlaceType::class)],
            'geographicParentPlaceId' => ['present_with:geographicPlaceId', 'nullable', 'uuid'],
            'geographicSelectionCursor' => ['present_with:geographicPlaceId', 'nullable', 'string', 'max:2048'],
            'geographicSelectionLimit' => ['required_with:geographicPlaceId', 'integer', 'min:1', 'max:100'],
        ];
    }
}
