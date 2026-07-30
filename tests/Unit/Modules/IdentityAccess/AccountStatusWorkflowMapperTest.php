<?php

namespace Tests\Unit\Modules\IdentityAccess;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusAction;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusActorId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusIntentId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusOccurredAt;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusTransition;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusVersion;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\AccountStatusWorkflowMapper;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AccountStatusWorkflowMapperTest extends TestCase
{
    public function test_transition_round_trips_to_a_verified_snapshot(): void
    {
        $mapper = new AccountStatusWorkflowMapper;
        $context = new AccountStatusContextV1(
            AccountTestData::id(),
            AccountStatusState::Active,
            new AccountStatusVersion(0),
            new AccountStatusVersion(0),
            AccountStatusAction::Suspend,
            new AccountStatusActorId('operator-1'),
            new AccountStatusOccurredAt(new DateTimeImmutable('2026-07-26T10:00:00+00:00')),
            new AccountStatusIntentId('intent-1'),
        );
        $row = $mapper->transition(
            new AccountStatusTransition(AccountStatusState::Active, AccountStatusAction::Suspend, AccountStatusState::Suspended),
            $context,
        );
        $row['entry_checksum'] = $row['checksum'];

        $snapshot = $mapper->snapshot($row);

        self::assertSame(AccountStatusState::Suspended, $snapshot->state);
        self::assertSame(1, $snapshot->version->value);
        self::assertSame('2026-07-26T10:00:00.000000Z', $row['occurred_at']);
    }

    public function test_corrupted_rows_are_rejected(): void
    {
        $mapper = new AccountStatusWorkflowMapper;
        $row = $mapper->bootstrap(AccountTestData::account());
        $row['entry_checksum'] = str_repeat('0', 64);

        $this->expectException(RuntimeException::class);
        $mapper->snapshot($row);
    }
}
