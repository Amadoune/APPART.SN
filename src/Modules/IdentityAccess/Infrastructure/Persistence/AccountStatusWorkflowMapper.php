<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusTransition;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusVersion;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\AccountStatusStoredState;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

final readonly class AccountStatusWorkflowMapper
{
    /** @return array<string, int|string|null> */
    public function bootstrap(Account $account): array
    {
        $row = [
            'account_id' => $account->id()->value,
            'version' => 0,
            'entry_kind' => 'bootstrap',
            'previous_state' => null,
            'current_state' => $account->isSuspended() ? 'suspended' : 'active',
            'action' => null,
            'actor_id' => null,
            'occurred_at' => null,
            'intent_id' => null,
            'context_version' => null,
            'legacy_account_version' => $account->version(),
        ];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @return array<string, int|string|null> */
    public function transition(AccountStatusTransition $transition, AccountStatusContextV1 $context): array
    {
        $row = [
            'account_id' => $context->accountId->value,
            'version' => $context->expectedVersion->value + 1,
            'entry_kind' => 'transition',
            'previous_state' => $transition->from->value,
            'current_state' => $transition->to->value,
            'action' => $transition->action->value,
            'actor_id' => $context->actorId->value,
            'occurred_at' => $this->canonical($context->occurredAt->value),
            'intent_id' => $context->intentId->value,
            'context_version' => $context->contractVersion->value,
            'legacy_account_version' => null,
        ];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @param array<string, mixed> $row */
    public function snapshot(array $row): AccountStatusStoredState
    {
        try {
            if (! hash_equals((string) $row['entry_checksum'], $this->checksum($row))) {
                throw new RuntimeException('Corrupted account status lifecycle entry.');
            }

            return new AccountStatusStoredState(
                AccountId::fromString((string) $row['account_id']),
                AccountStatusState::from((string) $row['current_state']),
                new AccountStatusVersion((int) $row['version']),
            );
        } catch (Throwable $error) {
            throw new RuntimeException('Invalid account status lifecycle persistence row.', 0, $error);
        }
    }

    /** @param array<string, mixed> $row */
    public function checksum(array $row): string
    {
        $values = [];
        foreach ([
            'account_id', 'version', 'entry_kind', 'previous_state',
            'current_state', 'action', 'actor_id', 'occurred_at', 'intent_id',
            'context_version', 'legacy_account_version',
        ] as $field) {
            $values[] = ($row[$field] ?? null) === null ? '' : (string) $row[$field];
        }

        return hash('sha256', implode("\n", $values));
    }

    private function canonical(DateTimeImmutable $value): string
    {
        return $value->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
