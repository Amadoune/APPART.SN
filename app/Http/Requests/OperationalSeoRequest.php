<?php

namespace App\Http\Requests;

use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoObservedAt;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;
use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class OperationalSeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['resourceKey' => ['bail', 'required', 'string', 'min:1', 'max:255'], 'observedAt' => ['bail', 'required', 'date_format:Y-m-d\TH:i:s.uP']];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (array_diff(array_keys($this->query()), array_keys($this->rules())) !== []) {
                $validator->errors()->add('_request', 'Unknown operational SEO fields are forbidden.');
            }
        });
    }

    public function resourceKey(): ContentSeoPublicResourceKey
    {
        return new ContentSeoPublicResourceKey((string) $this->validated('resourceKey'));
    }

    public function observedAt(): ContentSeoObservedAt
    {
        return new ContentSeoObservedAt(new DateTimeImmutable((string) $this->validated('observedAt')));
    }
}
