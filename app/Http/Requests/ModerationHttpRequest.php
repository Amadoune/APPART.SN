<?php

namespace App\Http\Requests;

use App\Application\ModerationHttp\ModerationHttpOperation;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use LogicException;

final class ModerationHttpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->attributes->get('iam_account_id') instanceof AccountId;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $common = $this->operation()->isMutation()
            ? [
                '_intentId' => ['bail', 'required', 'uuid'],
                'occurredAt' => ['bail', 'required', 'date_format:Y-m-d\TH:i:sP'],
                'policyVersion' => ['bail', 'required', 'string', 'min:1', 'max:64'],
            ]
            : [];

        return $common + match ($this->operation()) {
            ModerationHttpOperation::SubmitReport => [
                'targetType' => ['bail', 'required', Rule::in(['Listing', 'Media', 'Account', 'ProfessionalProfile'])],
                'targetId' => ['bail', 'required', 'uuid'],
                'category' => ['bail', 'required', 'string', 'min:1', 'max:64'],
                'statementReference' => ['bail', 'required', 'string', 'min:1', 'max:255'],
            ],
            ModerationHttpOperation::ValidateReport => [
                'caseId' => ['bail', 'required', 'uuid'],
                'disposition' => ['bail', 'required', Rule::in(['Accepted', 'Rejected'])],
                'reasonCode' => ['bail', 'required', 'string', 'min:1', 'max:64'],
                'expectedVersion' => ['bail', 'required', 'integer', 'min:1'],
            ],
            ModerationHttpOperation::RecordFinding => [
                'findingId' => ['bail', 'required', 'uuid'],
                'reportIds' => ['bail', 'required', 'array', 'min:1', 'max:100'],
                'reportIds.*' => ['bail', 'uuid'],
                'findingCode' => ['bail', 'required', 'string', 'min:1', 'max:64'],
                'evidenceReferences' => ['bail', 'required', 'array', 'min:1', 'max:100'],
                'evidenceReferences.*' => ['bail', 'string', 'min:1', 'max:255'],
                'expectedVersion' => ['bail', 'required', 'integer', 'min:1'],
            ],
            ModerationHttpOperation::IssueDecision => [
                'decisionId' => ['bail', 'required', 'uuid'],
                'findingIds' => ['bail', 'required', 'array', 'min:1', 'max:100'],
                'findingIds.*' => ['bail', 'uuid'],
                'disposition' => ['bail', 'required', 'string', 'min:1', 'max:64'],
                'targetAction' => ['bail', 'required', 'string', 'min:1', 'max:64'],
                'supersededDecisionId' => ['nullable', 'uuid'],
                'expectedVersion' => ['bail', 'required', 'integer', 'min:1'],
            ],
            ModerationHttpOperation::CloseCase => [
                'closureCode' => ['bail', 'required', 'string', 'min:1', 'max:64'],
                'expectedVersion' => ['bail', 'required', 'integer', 'min:1'],
            ],
            ModerationHttpOperation::ClaimQueueItem => [
                'leaseId' => ['bail', 'required', 'uuid'],
                'leaseExpiresAt' => ['bail', 'required', 'date_format:Y-m-d\TH:i:sP', 'after:occurredAt'],
            ],
            ModerationHttpOperation::ReadOwnReport => [],
            ModerationHttpOperation::ReadQueue => [
                'state' => ['bail', 'required', Rule::in(['Available', 'Claimed', 'Completed'])],
                'category' => ['nullable', 'string', 'min:1', 'max:64'],
                'cursor' => ['nullable', 'string', 'max:2048'],
                'limit' => ['bail', 'required', 'integer', 'min:1', 'max:100'],
            ],
            ModerationHttpOperation::ReadCase => [
                'viewLevel' => ['bail', 'required', Rule::in(['investigate', 'audit'])],
            ],
            ModerationHttpOperation::ReadDecision => [
                'decisionId' => ['nullable', 'uuid'],
                'purpose' => ['bail', 'required', Rule::in(['decide', 'audit'])],
            ],
        };
    }

    protected function prepareForValidation(): void
    {
        if ($this->operation()->isMutation()) {
            $this->merge(['_intentId' => $this->header('Idempotency-Key')]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (array_diff(array_keys($this->all()), array_keys($this->rules())) !== []) {
                $validator->errors()->add('_request', 'Unknown moderation fields are forbidden.');
            }
        });
    }

    public function operation(): ModerationHttpOperation
    {
        return ModerationHttpOperation::from((string) $this->route('moderation_operation'));
    }

    public function accountId(): AccountId
    {
        $account = $this->attributes->get('iam_account_id');
        if (! $account instanceof AccountId) {
            throw new LogicException('The authenticated AccountId is required.');
        }

        return $account;
    }
}
