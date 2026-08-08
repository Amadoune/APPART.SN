<?php

namespace App\Http\Requests;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionObservedAt;
use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class SearchQueryResolutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'query' => ['bail', 'required', 'string', 'min:1', 'max:2048'],
            'observedAt' => ['bail', 'required', 'date_format:Y-m-d\TH:i:s.uP'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (array_diff(array_keys($this->query()), array_keys($this->rules())) !== []) {
                $validator->errors()->add('_request', 'Unknown search query resolution fields are forbidden.');
            }
        });
    }

    public function searchQuery(): SearchQuery
    {
        return new SearchQuery((string) $this->validated('query'));
    }

    public function observedAt(): SearchQueryResolutionObservedAt
    {
        return new SearchQueryResolutionObservedAt(
            new DateTimeImmutable((string) $this->validated('observedAt')),
        );
    }
}
