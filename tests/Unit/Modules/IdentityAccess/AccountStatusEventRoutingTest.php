<?php

namespace Tests\Unit\Modules\IdentityAccess;

use App\Application\AccountStatusEventRouting\AccountStatusRoutingDestination;
use App\Application\AccountStatusEventRouting\AccountStatusRoutingDiagnostic;
use App\Application\AccountStatusEventRouting\AccountStatusRoutingResult;
use App\Application\AccountStatusEventRouting\AccountStatusRoutingStatus;
use App\Application\AccountStatusEventRouting\DeterministicAccountStatusEventRouter;
use App\Application\AccountStatusEventTransport\AccountStatusDeliveryMessage;
use App\Application\AccountStatusEventTransport\AccountStatusDeliveryPayload;
use App\Application\AccountStatusEventTransport\AccountStatusTransportSerializer;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventCatalog;
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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AccountStatusEventRoutingTest extends TestCase
{
    /** @return iterable<string, array{AccountStatusTransition}> */
    public static function transitions(): iterable
    {
        yield 'suspended' => [new AccountStatusTransition(
            AccountStatusState::Active,
            AccountStatusAction::Suspend,
            AccountStatusState::Suspended,
        )];
        yield 'reactivated' => [new AccountStatusTransition(
            AccountStatusState::Suspended,
            AccountStatusAction::Reactivate,
            AccountStatusState::Active,
        )];
    }

    #[DataProvider('transitions')]
    public function test_certified_message_type_selects_the_closed_destination(
        AccountStatusTransition $transition,
    ): void {
        $message = self::message($transition);
        $result = (new DeterministicAccountStatusEventRouter(
            new AccountStatusTransportSerializer,
        ))->route($message);

        self::assertSame(AccountStatusRoutingStatus::Routed, $result->status);
        self::assertSame(AccountStatusRoutingDestination::LifecycleFacts, $result->destination);
        self::assertNull($result->diagnostic);
        self::assertSame($message, $result->message);
        self::assertSame($message->messageId->value, $result->message->messageId->value);
        self::assertSame($message->metadata->eventId, $result->message->metadata->eventId);
        self::assertSame($message->payload->fields(), $result->message->payload->fields());
    }

    public function test_status_destination_and_diagnostics_are_closed(): void
    {
        self::assertSame(['routed', 'rejected'], array_column(AccountStatusRoutingStatus::cases(), 'value'));
        self::assertSame(
            ['identity_access.account_status.lifecycle_facts'],
            array_column(AccountStatusRoutingDestination::cases(), 'value'),
        );
        self::assertSame(
            ['unsupported_message', 'corrupted_message'],
            array_column(AccountStatusRoutingDiagnostic::cases(), 'value'),
        );

        $message = self::message(iterator_to_array(self::transitions())['suspended'][0]);
        $rejected = AccountStatusRoutingResult::rejected(
            $message,
            AccountStatusRoutingDiagnostic::CorruptedMessage,
        );
        self::assertSame(AccountStatusRoutingStatus::Rejected, $rejected->status);
        self::assertNull($rejected->destination);
    }

    private static function message(AccountStatusTransition $transition): AccountStatusDeliveryMessage
    {
        $context = new AccountStatusContextV1(
            AccountId::fromString('49f00000-0000-4000-8000-000000000001'),
            $transition->from,
            new AccountStatusVersion(7),
            new AccountStatusVersion(7),
            $transition->action,
            new AccountStatusActorId('operator-49h'),
            new AccountStatusOccurredAt(new DateTimeImmutable('2026-07-26T13:00:00+00:00')),
            new AccountStatusIntentId('intent-49h'),
        );
        $event = (new AccountStatusEventCatalog)->eventFor($transition, $context, 8);

        return AccountStatusDeliveryMessage::wrap(new AccountStatusDeliveryPayload($event));
    }
}
