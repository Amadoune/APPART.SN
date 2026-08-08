<?php

namespace App\Http\Requests;

use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;
use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class PrivacyPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['subjectKey' => ['bail', 'required', 'string', 'min:1', 'max:255'], 'observedAt' => ['bail', 'required', 'date_format:Y-m-d\TH:i:s.uP']];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (array_diff(array_keys($this->query()), array_keys($this->rules())) !== []) {
                $validator->errors()->add('_request', 'Unknown privacy policy fields are forbidden.');
            }
        });
    }

    public function subjectKey(): SecurityComplianceSubjectKey
    {
        return new SecurityComplianceSubjectKey((string) $this->validated('subjectKey'));
    }

    public function observedAt(): SecurityComplianceObservedAt
    {
        return new SecurityComplianceObservedAt(new DateTimeImmutable((string) $this->validated('observedAt')));
    }
}
