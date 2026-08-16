<?php

namespace App\Http\Requests;

use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class PropertyAuthoringGeographySelectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->attributes->has('iam_account_id');
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'type' => ['bail', 'required', Rule::enum(PlaceType::class)],
            'parentPlaceId' => ['nullable', 'regex:/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/'],
            'cursor' => ['nullable', 'string', 'max:2048'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (array_diff(array_keys($this->query()), array_keys($this->rules())) !== []) {
                $validator->errors()->add('_request', 'Unknown Geography selection parameters are forbidden.');
            }
            $country = $this->query('type') === PlaceType::Country->value;
            $hasParent = is_string($this->query('parentPlaceId')) && $this->query('parentPlaceId') !== '';
            if ($country === $hasParent) {
                $validator->errors()->add('parentPlaceId', 'The Geography selection parent does not match the requested type.');
            }
        });
    }

    protected function failedValidation(ValidatorContract $validator): never
    {
        throw new HttpResponseException(new JsonResponse(
            ['error' => 'invalid_query'],
            422,
            ['Cache-Control' => 'no-store', 'Pragma' => 'no-cache', 'X-Content-Type-Options' => 'nosniff'],
        ));
    }
}
