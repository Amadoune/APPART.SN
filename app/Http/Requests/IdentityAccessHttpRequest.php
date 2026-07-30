<?php

namespace App\Http\Requests;

use App\Application\IdentityAccessHttp\IdentityAccessHttpOperation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class IdentityAccessHttpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->operation() === IdentityAccessHttpOperation::Login
            || $this->operation() === IdentityAccessHttpOperation::RequestRecovery
            || $this->operation() === IdentityAccessHttpOperation::CompleteRecovery
            || $this->attributes->has('iam_account_id');
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $common = [
            '_intentId' => $this->isRead()
                ? ['nullable']
                : ['bail', 'required', 'uuid'],
        ];

        return $common + match ($this->operation()) {
            IdentityAccessHttpOperation::Login => [
                'identifier' => ['bail', 'required', 'string', 'min:3', 'max:320'],
                'credential' => ['bail', 'required', 'string', 'min:8', 'max:1024'],
                'requestedAt' => $this->dateRule(),
            ],
            IdentityAccessHttpOperation::RequestRecovery => [
                'identifier' => ['bail', 'required', 'string', 'min:3', 'max:320'],
                'requestedAt' => $this->dateRule(),
            ],
            IdentityAccessHttpOperation::CompleteRecovery => [
                'challenge' => ['bail', 'required', 'string', 'uuid'],
                'proof' => ['bail', 'required', 'string', 'min:32', 'max:1024'],
                'newCredential' => ['bail', 'required', 'string', 'min:12', 'max:1024'],
                'requestedAt' => $this->dateRule(),
            ],
            IdentityAccessHttpOperation::MutateProfile => [
                'displayName' => ['bail', 'required', 'string', 'min:1', 'max:160'],
                'requestedAt' => $this->dateRule(),
            ],
            IdentityAccessHttpOperation::RequestContactChange => [
                'channel' => ['bail', 'required', 'string', Rule::in(['email', 'phone'])],
                'value' => ['bail', 'required', 'string', 'min:3', 'max:320'],
                'requestedAt' => $this->dateRule(),
            ],
            IdentityAccessHttpOperation::VerifyContactChange => [
                'changeId' => ['bail', 'required', 'uuid'],
                'proof' => ['bail', 'required', 'string', 'min:32', 'max:1024'],
                'requestedAt' => $this->dateRule(),
            ],
            IdentityAccessHttpOperation::RequestClosure => [
                'reasonCategory' => ['bail', 'required', 'string', Rule::in(['UserRequested', 'Security', 'Other'])],
                'requestedAt' => $this->dateRule(),
            ],
            IdentityAccessHttpOperation::ConfirmClosure,
            IdentityAccessHttpOperation::Reopen,
            IdentityAccessHttpOperation::Logout,
            IdentityAccessHttpOperation::RenewSession => [
                'requestedAt' => $this->dateRule(),
            ],
            IdentityAccessHttpOperation::ListSessions,
            IdentityAccessHttpOperation::ReadProfile => [],
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
            $publicAllowed = array_values(array_filter($allowed, static fn (string $field): bool => ! str_starts_with($field, '_')));
            if (array_diff(array_keys($this->all()), [...$publicAllowed, '_intentId']) !== []) {
                $validator->errors()->add('_request', 'Unknown Identity & Access fields are forbidden.');
            }
        });
    }

    public function operation(): IdentityAccessHttpOperation
    {
        return IdentityAccessHttpOperation::from((string) $this->route('iam_operation'));
    }

    public function isRead(): bool
    {
        return in_array($this->operation(), [
            IdentityAccessHttpOperation::ListSessions,
            IdentityAccessHttpOperation::ReadProfile,
        ], true);
    }

    /** @return list<string> */
    private function dateRule(): array
    {
        return ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/'];
    }
}
