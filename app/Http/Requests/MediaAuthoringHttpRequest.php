<?php

namespace App\Http\Requests;

use App\Application\MediaAuthoringHttp\MediaAuthoringHttpOperation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class MediaAuthoringHttpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->attributes->has('iam_account_id');
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return match ($this->operation()) {
            MediaAuthoringHttpOperation::Upload => [
                '_intentId' => ['bail', 'required', 'uuid'],
                'image' => ['bail', 'required', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:10240'],
                'order' => ['bail', 'required', 'integer', 'min:1', 'max:10000'],
                'caption' => ['nullable', 'string', 'min:1', 'max:500'],
            ],
            MediaAuthoringHttpOperation::Collection => [],
            MediaAuthoringHttpOperation::Archive => [
                'replacementMediaId' => ['nullable', 'uuid'],
            ],
        };
    }

    protected function prepareForValidation(): void
    {
        if ($this->operation() === MediaAuthoringHttpOperation::Upload) {
            $this->merge(['_intentId' => $this->header('Idempotency-Key')]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (array_diff(array_keys($this->all()), array_keys($this->rules())) !== []) {
                $validator->errors()->add('_request', 'Unknown media authoring fields are forbidden.');
            }
        });
    }

    public function operation(): MediaAuthoringHttpOperation
    {
        return MediaAuthoringHttpOperation::from((string) $this->route('media_authoring_operation'));
    }
}
