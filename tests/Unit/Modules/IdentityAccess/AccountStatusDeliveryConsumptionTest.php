<?php

namespace Tests\Unit\Modules\IdentityAccess;

use App\Application\AccountStatusEventConsumption\AccountStatusConsumptionDiagnostic;
use App\Application\AccountStatusEventConsumption\AccountStatusConsumptionResult;
use App\Application\AccountStatusEventConsumption\AccountStatusConsumptionStatus;
use App\Application\AccountStatusEventConsumption\AccountStatusDeliveryConsumer;
use App\Application\AccountStatusEventRouting\AccountStatusRoutingDestination;
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

final class AccountStatusDeliveryConsumptionTest extends TestCase
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
    public function test_routed_message_is_consumed_without_identity_or_payload_divergence(
        AccountStatusTransition $transition,
    ): void {
        $message = self::message($transition);
        $result = (new AccountStatusDeliveryConsumer(
            new AccountStatusTransportSerializer,
        ))->consume($message, AccountStatusRoutingDestination::LifecycleFacts);

        self::assertSame(AccountStatusConsumptionStatus::Consumed, $result->status);
        self::assertNull($result->diagnostic);
        self::assertNotNull($result->fact);
        self::assertSame($message, $result->fact->message);
        self::assertSame($message->messageId->value, $result->fact->messageId);
        self::assertSame($message->metadata->eventId, $result->fact->eventId);
        self::assertSame($message->payload->checksum(), $result->fact->payloadChecksum);
        self::assertSame(AccountStatusRoutingDestination::LifecycleFacts, $result->fact->destination);
        self::assertSame($message->payload->event->contract(), $result->fact->event->contract());
    }

    public function test_statuses_and_rejection_diagnostics_are_closed(): void
    {
        self::assertSame(['consumed', 'rejected'], array_column(AccountStatusConsumptionStatus::cases(), 'value'));
        self::assertSame(
            ['unsupported_message', 'corrupted_message', 'unsupported_destination'],
            array_column(AccountStatusConsumptionDiagnostic::cases(), 'value'),
        );
        foreach (AccountStatusConsumptionDiagnostic::cases() as $diagnostic) {
            $result = AccountStatusConsumptionResult::rejected($diagnostic);
            self::assertSame(AccountStatusConsumptionStatus::Rejected, $result->status);
            self::assertSame($diagnostic, $result->diagnostic);
            self::assertNull($result->fact);
        }
    }

    private static function message(AccountStatusTransition $transition): AccountStatusDeliveryMessage
    {
        $context = new AccountStatusContextV1(
            AccountId::fromString('49f00000-0000-4000-8000-000000000001'),
            $transition->from,
            new AccountStatusVersion(7),
            new AccountStatusVersion(7),
            $transition->action,
            new AccountStatusActorId('operator-49i'),
            new AccountStatusOccurredAt(new DateTimeImmutable('2026-07-26T14:00:00+00:00')),
            new AccountStatusIntentId('intent-49i'),
        );
        $event = (new AccountStatusEventCatalog)->eventFor($transition, $context, 8);

        return AccountStatusDeliveryMessage::wrap(new AccountStatusDeliveryPayload($event));
    }
}
