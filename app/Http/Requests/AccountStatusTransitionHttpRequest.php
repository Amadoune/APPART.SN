<?php

namespace App\Http\Requests;

use App\Application\AccountStatusEventIntegration\AccountStatusAtomicEventRequest;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusAction;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusActorId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextVersion;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusIntentId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusOccurredAt;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusVersion;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class AccountStatusTransitionHttpRequest extends FormRequest
{
    /** @var list<string> */
    private const array FIELDS = [
        'currentState',
        'contextVersion',
        'expectedVersion',
        'observedVersion',
        'actorId',
        'occurredAt',
        'recordedAt',
        'intentId',
    ];

    public function authorize(): bool
    {
        return $this->attributes->get('account_status_authorized') === true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'currentState' => ['bail', 'required', 'string', Rule::in(
                array_column(AccountStatusState::cases(), 'value'),
            )],
            'contextVersion' => ['bail', 'required', 'integer', Rule::in([
                AccountStatusContextVersion::V1->value,
            ])],
            'expectedVersion' => ['bail', 'required', 'integer', 'min:0'],
            'observedVersion' => ['bail', 'required', 'integer', 'min:0'],
            'actorId' => ['bail', 'required', 'string', 'regex:/^[A-Za-z0-9][A-Za-z0-9._:-]{2,127}$/'],
            'occurredAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/'],
            'recordedAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/', 'after_or_equal:occurredAt'],
            'intentId' => ['bail', 'required', 'string', 'regex:/^[A-Za-z0-9][A-Za-z0-9._:-]{2,127}$/', 'different:actorId'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (array_diff(array_keys($this->all()), self::FIELDS) !== []) {
                $validator->errors()->add(
                    '_request',
                    'Unknown Account Status fields are forbidden.',
                );
            }
        });
    }

    public function applicationRequest(
        AccountStatusAction $action,
    ): AccountStatusAtomicEventRequest {
        /** @var array{currentState:string,contextVersion:int,expectedVersion:int,observedVersion:int,actorId:string,occurredAt:string,recordedAt:string,intentId:string} $input */
        $input = $this->validated();
        $context = new AccountStatusContextV1(
            AccountId::fromString((string) $this->route('accountId')),
            AccountStatusState::from($input['currentState']),
            new AccountStatusVersion($input['expectedVersion']),
            new AccountStatusVersion($input['observedVersion']),
            $action,
            new AccountStatusActorId($input['actorId']),
            new AccountStatusOccurredAt(new DateTimeImmutable($input['occurredAt'])),
            new AccountStatusIntentId($input['intentId']),
        );

        return new AccountStatusAtomicEventRequest(
            $context,
            new DateTimeImmutable($input['recordedAt']),
        );
    }
}
