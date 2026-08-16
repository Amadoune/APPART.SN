<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class PublicationReviewExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->attributes->has('iam_account_id');
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return match ((string) $this->route('review_operation')) {
            'claim' => [
                'commandId' => ['required', 'uuid'],
                'expectedVersion' => ['required', 'integer', 'min:1'],
                'occurredAt' => ['required', 'date_format:Y-m-d\TH:i:s.uP'],
            ],
            'begin' => [
                'commandId' => ['required', 'uuid'],
                'expectedVersion' => ['required', 'integer', 'min:1'],
                'occurredAt' => ['required', 'date_format:Y-m-d\TH:i:s.uP'],
                'listingId' => ['required', 'uuid'],
                'submissionVersion' => ['required', 'integer', 'min:1'],
            ],
            'approve' => [
                'commandId' => ['required', 'uuid'],
                'projectionCommandId' => ['required', 'uuid', 'different:commandId'],
                'expectedVersion' => ['required', 'integer', 'min:1'],
                'occurredAt' => ['required', 'date_format:Y-m-d\TH:i:s.uP'],
                'listingId' => ['required', 'uuid'],
                'submissionVersion' => ['required', 'integer', 'min:1'],
            ],
            default => [],
        };
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowed = [...array_keys($this->rules()), '_token'];
            if (array_diff(array_keys($this->all()), $allowed) !== []) {
                $validator->errors()->add('_request', 'Unknown publication review fields are forbidden.');
            }
        });
    }
}
