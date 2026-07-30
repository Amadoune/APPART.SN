<?php

namespace Tests\Unit\Modules\IdentityAccess;

use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventCatalog;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventPayloadVersion;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventType;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusAction;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusActorId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusIntentId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusOccurredAt;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusTransition;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusVersion;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AccountStatusEventContractTest extends TestCase
{
    /** @return iterable<string, array{AccountStatusTransition, AccountStatusEventType}> */
    public static function transitions(): iterable
    {
        yield 'suspended' => [
            new AccountStatusTransition(
                AccountStatusState::Active,
                AccountStatusAction::Suspend,
                AccountStatusState::Suspended,
            ),
            AccountStatusEventType::Suspended,
        ];
        yield 'reactivated' => [
            new AccountStatusTransition(
                AccountStatusState::Suspended,
                AccountStatusAction::Reactivate,
                AccountStatusState::Active,
            ),
            AccountStatusEventType::Reactivated,
        ];
    }

    #[DataProvider('transitions')]
    public function test_certified_transition_produces_one_minimal_immutable_v1_fact(
        AccountStatusTransition $transition,
        AccountStatusEventType $type,
    ): void {
        $event = (new AccountStatusEventCatalog)->eventFor($transition, $this->context($transition), 8);

        self::assertSame($type, $event->type);
        self::assertSame(AccountStatusEventPayloadVersion::V1, $event->payload->version);
        self::assertSame(
            ['eventId', 'eventType', 'payloadVersion', 'occurredAt', 'payload'],
            array_keys($event->contract()),
        );
        self::assertSame(
            ['accountId', 'previousState', 'action', 'currentState', 'occurredVersion'],
            array_keys($event->payload->fields()),
        );
    }

    public function test_identity_and_contract_are_deterministic_and_exclude_private_context(): void
    {
        [$transition] = array_values(iterator_to_array(self::transitions()))[0];
        $catalog = new AccountStatusEventCatalog;
        $context = $this->context($transition);
        $first = $catalog->eventFor($transition, $context, 8);
        $same = $catalog->eventFor($transition, $context, 8);
        $serialized = json_encode($first->contract(), JSON_THROW_ON_ERROR);

        self::assertSame($first->eventId->value, $same->eventId->value);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first->eventId->value);
        foreach (['actorId', 'intentId', 'expectedVersion', 'observedVersion', 'historicalVersion'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $serialized);
        }
    }

    public function test_types_versions_and_transition_matrix_are_closed(): void
    {
        self::assertSame(
            ['account.status.suspended', 'account.status.reactivated'],
            array_column(AccountStatusEventType::cases(), 'value'),
        );
        self::assertSame([1], array_column(AccountStatusEventPayloadVersion::cases(), 'value'));

        $this->expectException(DomainException::class);
        (new AccountStatusEventCatalog)->typeFor(
            new AccountStatusTransition(
                AccountStatusState::Active,
                AccountStatusAction::Reactivate,
                AccountStatusState::Suspended,
            ),
        );
    }

    private function context(AccountStatusTransition $transition): AccountStatusContextV1
    {
        return new AccountStatusContextV1(
            AccountId::fromString('49f00000-0000-4000-8000-000000000001'),
            $transition->from,
            new AccountStatusVersion(7),
            new AccountStatusVersion(7),
            $transition->action,
            new AccountStatusActorId('operator-49f'),
            new AccountStatusOccurredAt(new DateTimeImmutable('2026-07-26T11:00:00+00:00')),
            new AccountStatusIntentId('intent-49f'),
        );
    }
}
