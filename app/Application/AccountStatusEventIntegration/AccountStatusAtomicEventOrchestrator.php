<?php

namespace App\Application\AccountStatusEventIntegration;

use App\Application\AccountStatusEventIntegration\Contract\AccountStatusAtomicTransaction;
use App\Application\AccountStatusEventRouting\AccountStatusEventRouter;
use App\Application\AccountStatusEventRouting\AccountStatusRoutingStatus;
use App\Application\AccountStatusEventTransport\AccountStatusDeliveryMessage;
use App\Application\AccountStatusEventTransport\AccountStatusDeliveryPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryDestination;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPublishableFact;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionDelivery\PublicProjectionRoutedDeliveryMessageV1;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxRoutedWriterV1;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventCatalog;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusTransition;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\AccountStatusOrchestrationResult;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\AccountStatusOrchestrationStatus;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\Contract\AccountStatusOrchestrator;
use Throwable;

final readonly class AccountStatusAtomicEventOrchestrator
{
    public function __construct(
        private AccountStatusOrchestrator $orchestrator,
        private AccountStatusAtomicTransaction $transaction,
        private AccountStatusEventCatalog $events,
        private PublicProjectionDeliveryCatalogMessageFactory $messages,
        private AccountStatusEventRouter $router,
        private PublicProjectionOutboxRoutedWriterV1 $outbox,
        private PublicProjectionOutboxConsumerId $consumerId,
    ) {}

    public function transition(
        AccountStatusAtomicEventRequest $request,
    ): AccountStatusOrchestrationResult {
        try {
            return $this->transaction->run(function () use ($request): AccountStatusOrchestrationResult {
                $result = $this->orchestrator->transition($request->context);
                if ($result->status !== AccountStatusOrchestrationStatus::Applied
                    || $result->state === null) {
                    return $result;
                }

                $transition = new AccountStatusTransition(
                    $request->context->currentState,
                    $request->context->action,
                    $result->state,
                );
                $occurredVersion = $request->context->expectedVersion->value + 1;
                $event = $this->events->eventFor(
                    $transition,
                    $request->context,
                    $occurredVersion,
                );
                $payload = new AccountStatusDeliveryPayload($event);
                $transportMessage = AccountStatusDeliveryMessage::wrap($payload);
                $routing = $this->router->route($transportMessage);
                if ($routing->status !== AccountStatusRoutingStatus::Routed
                    || $routing->destination === null) {
                    throw new AccountStatusAtomicEventIntegrationFailure(
                        'Account Status event routing was rejected.',
                    );
                }

                $fact = new PublicProjectionDeliveryPublishableFact(
                    PublicProjectionDeliveryEventType::fromString($event->type->value),
                    PublicProjectionDeliveryPayloadVersion::fromInt($event->payload->version->value),
                    PublicProjectionDeliverySourceModule::fromString('IdentityAccess'),
                    PublicProjectionDeliveryAggregateType::fromString('AccountStatus'),
                    PublicProjectionDeliveryAggregateId::fromString($event->payload->accountId->value),
                    new PublicProjectionDeliveryOrder(
                        $occurredVersion,
                        PublicProjectionDeliveryEventIndex::fromInt(1),
                    ),
                    $event->occurredAt->value,
                    $payload,
                );
                $message = $this->messages->create($fact, $request->recordedAt);
                $delivery = PublicProjectionRoutedDeliveryMessageV1::fromDecision(
                    $message,
                    PublicProjectionDeliveryDestination::fromString(
                        $routing->destination->value,
                    ),
                );
                $written = $this->outbox->appendRouted($delivery, $this->consumerId);
                if (! in_array($written, [
                    PublicProjectionOutboxWriteResult::Applied,
                    PublicProjectionOutboxWriteResult::AlreadyApplied,
                ], true)) {
                    throw new AccountStatusAtomicEventIntegrationFailure(
                        'Account Status routed event Outbox write was rejected.',
                    );
                }

                return $result;
            });
        } catch (Throwable) {
            return new AccountStatusOrchestrationResult(
                AccountStatusOrchestrationStatus::PersistenceCorrupted,
                null,
            );
        }
    }
}
