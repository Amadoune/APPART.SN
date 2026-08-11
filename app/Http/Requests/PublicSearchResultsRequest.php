<?php

namespace App\Http\Requests;

use App\Application\PublicSearchResults\PublicSearchResultsQuery;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class PublicSearchResultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'limit' => ['sometimes', 'integer', 'min:1', 'max:24'],
            'after' => ['sometimes', 'string', 'min:1', 'max:512', 'regex:/^annonces\/[a-z0-9][a-z0-9-]*$/'],
            'transaction' => ['sometimes', 'string', 'in:sale,rent'],
            'city' => ['sometimes', 'string', 'min:1', 'max:80'],
            'propertyType' => ['sometimes', 'string', 'min:1', 'max:80'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (array_diff(array_keys($this->query()), array_keys($this->rules())) !== []) {
                $validator->errors()->add('_request', 'Unknown public search result fields are forbidden.');
            }
        });
    }

    public function publicQuery(): PublicSearchResultsQuery
    {
        return new PublicSearchResultsQuery(
            limit: (int) $this->validated('limit', 12),
            afterCanonicalPath: $this->validated('after'),
            transaction: $this->validated('transaction'),
            city: $this->validated('city'),
            propertyType: $this->validated('propertyType'),
        );
    }
}
