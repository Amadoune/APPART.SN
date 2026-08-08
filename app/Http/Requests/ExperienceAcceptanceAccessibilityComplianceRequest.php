<?php

namespace App\Http\Requests;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class ExperienceAcceptanceAccessibilityComplianceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['observedAt' => ['bail', 'required', 'date_format:Y-m-d\\TH:i:s.uP']];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (array_diff(array_keys($this->query()), ['observedAt']) !== []) {
                $validator->errors()->add('_request', 'Unknown experience acceptance fields are forbidden.');
            }
        });
    }

    public function observedAt(): ExperienceAcceptanceObservedAt
    {
        return new ExperienceAcceptanceObservedAt(new DateTimeImmutable((string) $this->validated('observedAt')));
    }
}
