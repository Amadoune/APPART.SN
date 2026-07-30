<?php

namespace App\Application\ModerationListingHandoff;

use App\Application\ModerationEventOutbox\Contract\ModerationOutboxReaderV1;
use App\Application\ModerationEventOutbox\ModerationOutboxDelivery;
use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationListingHandoff\Contract\ModerationListingHandoffResultStore;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\Contract\ModeratorAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModerationCapabilityV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModeratorAuthorizationDecisionV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\Contract\ListingModerationCommandGatewayV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\Contract\ListingModerationReaderV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationActionV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationCommandResultV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationEligibilityV1;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationCaseStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationDecisionStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceReadStatus;
use DateTimeImmutable;
use Throwable;

final readonly class ModerationListingHandoffConsumer
{
    private const int MAX_ATTEMPTS = 3;

    public function __construct(
        private ModerationOutboxReaderV1 $outbox,
        private ModeratorAuthorizationReaderV1 $authorization,
        private ListingModerationReaderV1 $listingReader,
        private ListingModerationCommandGatewayV1 $listingGateway,
        private ModerationCaseStore $cases,
        private ModerationDecisionStore $decisions,
        private ModerationListingHandoffResultStore $results,
        private ModerationListingHandoffTerminalIntegrator $terminalIntegrator,
    ) {}

    public function consumeNext(string $owner, DateTimeImmutable $now): ?ModerationListingHandoffStatus
    {
        try {
            $delivery = $this->outbox->claimNextForDestination(
                $owner,
                ModerationRoutingDestination::ListingHandoff,
                $now,
            );
            if ($delivery === null) {
                return null;
            }

            return $this->consume($delivery, $now);
        } catch (Throwable) {
            return ModerationListingHandoffStatus::DependencyUnavailable;
        }
    }

    private function consume(
        ModerationOutboxDelivery $delivery,
        DateTimeImmutable $now,
    ): ModerationListingHandoffStatus {
        $event = $delivery->message->event;
        if ($event->type !== ModerationEventTypeV1::DecisionIssued) {
            return $this->quarantine($delivery, $this->emptyRecord($delivery, $now), 'unexpected_event');
        }
        $decisionId = $event->payload['decisionId'] ?? null;
        if (! is_string($decisionId)) {
            return $this->quarantine($delivery, $this->emptyRecord($delivery, $now), 'corrupted_event');
        }
        $case = $this->cases->read($event->caseId);
        if ($case->status === ModerationPersistenceReadStatus::DependencyUnavailable) {
            return $this->retry($delivery, $this->record($delivery, $decisionId, '', '', ModerationListingHandoffStatus::DependencyUnavailable, $now), $now);
        }
        if ($case->status !== ModerationPersistenceReadStatus::Found || $case->state === null || $case->state->targetType !== 'Listing') {
            return $this->quarantine($delivery, $this->record($delivery, $decisionId, '', '', ModerationListingHandoffStatus::Quarantined, $now), 'corrupted_case');
        }
        $decision = $this->decisions->read($event->caseId, $decisionId);
        if ($decision->status === ModerationPersistenceReadStatus::DependencyUnavailable) {
            return $this->retry($delivery, $this->record($delivery, $decisionId, '', '', ModerationListingHandoffStatus::DependencyUnavailable, $now), $now);
        }
        if ($decision->status !== ModerationPersistenceReadStatus::Found || $decision->decision === null) {
            return $this->quarantine($delivery, $this->record($delivery, $decisionId, '', '', ModerationListingHandoffStatus::Quarantined, $now), 'corrupted_decision');
        }

        $actor = $decision->decision->payload['actorAccountId'] ?? null;
        $targetAction = $decision->decision->payload['targetAction'] ?? null;
        if (! is_string($actor) || ! is_string($targetAction)) {
            return $this->quarantine($delivery, $this->record($delivery, $decisionId, '', '', ModerationListingHandoffStatus::Quarantined, $now), 'corrupted_decision');
        }
        try {
            $accountId = AccountId::fromString($actor);
            $listingId = ListingId::fromString($case->state->targetId);
            $action = ListingModerationActionV1::from($targetAction);
        } catch (Throwable) {
            return $this->quarantine($delivery, $this->record($delivery, $decisionId, '', '', ModerationListingHandoffStatus::Quarantined, $now), 'corrupted_decision');
        }

        $commandId = $event->eventId;
        $checksum = hash('sha256', implode("\n", [
            'moderation-listing-handoff-v1',
            $listingId->value,
            $action->value,
            $commandId,
            $event->occurredAt->format('Y-m-d\TH:i:s.uP'),
        ]));
        $requested = $this->record(
            $delivery,
            $decisionId,
            $commandId,
            $checksum,
            ModerationListingHandoffStatus::Requested,
            $now,
        );
        if (! $this->results->append($requested)) {
            return $this->retry($delivery, $requested, $now);
        }

        $authorization = $this->authorization->authorize(
            $accountId,
            ModerationCapabilityV1::Decide,
            $event->occurredAt,
        );
        if ($authorization !== ModeratorAuthorizationDecisionV1::Allowed) {
            return match ($authorization) {
                ModeratorAuthorizationDecisionV1::Denied => $this->terminal(
                    $delivery,
                    $requested,
                    ModerationListingHandoffStatus::AuthorizationDenied,
                    $now,
                ),
                ModeratorAuthorizationDecisionV1::Corrupted => $this->quarantine(
                    $delivery,
                    $requested,
                    'corrupted_authorization',
                ),
                ModeratorAuthorizationDecisionV1::DependencyUnavailable => $this->retry(
                    $delivery,
                    $requested,
                    $now,
                ),
            };
        }

        $eligibility = $this->listingReader->read($listingId, $event->occurredAt);
        if ($eligibility !== ListingModerationEligibilityV1::Eligible) {
            return match ($eligibility) {
                ListingModerationEligibilityV1::Ineligible,
                ListingModerationEligibilityV1::Missing => $this->terminal(
                    $delivery,
                    $requested,
                    ModerationListingHandoffStatus::TargetIneligible,
                    $now,
                ),
                ListingModerationEligibilityV1::Corrupted => $this->quarantine(
                    $delivery,
                    $requested,
                    'corrupted_target',
                ),
                ListingModerationEligibilityV1::DependencyUnavailable => $this->retry(
                    $delivery,
                    $requested,
                    $now,
                ),
            };
        }

        return match ($this->listingGateway->apply(
            $listingId,
            $action,
            $commandId,
            $checksum,
            $event->occurredAt,
        )) {
            ListingModerationCommandResultV1::Applied => $this->terminal($delivery, $requested, ModerationListingHandoffStatus::Applied, $now),
            ListingModerationCommandResultV1::AlreadyApplied => $this->terminal($delivery, $requested, ModerationListingHandoffStatus::AlreadyApplied, $now),
            ListingModerationCommandResultV1::Rejected => $this->terminal($delivery, $requested, ModerationListingHandoffStatus::Rejected, $now),
            ListingModerationCommandResultV1::VersionConflict => $this->terminal($delivery, $requested, ModerationListingHandoffStatus::VersionConflict, $now),
            ListingModerationCommandResultV1::DivergentIntent => $this->quarantine($delivery, $requested, 'divergent_intent'),
            ListingModerationCommandResultV1::DependencyUnavailable => $this->retry($delivery, $requested, $now),
        };
    }

    private function terminal(
        ModerationOutboxDelivery $delivery,
        ModerationListingHandoffRecord $requested,
        ModerationListingHandoffStatus $status,
        DateTimeImmutable $now,
    ): ModerationListingHandoffStatus {
        $successful = in_array($status, [
            ModerationListingHandoffStatus::Applied,
            ModerationListingHandoffStatus::AlreadyApplied,
        ], true);
        $integrated = $successful
            ? $this->terminalIntegrator->integrate($delivery, $requested, $status, $now)
            : $this->results->append($this->withStatus($requested, $status, $now));
        if (! $integrated) {
            return $this->retry($delivery, $requested, $now);
        }
        if (! $this->outbox->markDelivered($delivery, $now)) {
            return ModerationListingHandoffStatus::DependencyUnavailable;
        }

        return $status;
    }

    private function retry(
        ModerationOutboxDelivery $delivery,
        ModerationListingHandoffRecord $record,
        DateTimeImmutable $now,
    ): ModerationListingHandoffStatus {
        $this->results->append($this->withStatus($record, ModerationListingHandoffStatus::DependencyUnavailable, $now));
        if ($delivery->attempt >= self::MAX_ATTEMPTS) {
            return $this->quarantine($delivery, $record, 'retry_exhausted');
        }
        $this->outbox->retry($delivery, $now->modify('+'.$delivery->attempt.' seconds'), 'dependency_unavailable');

        return ModerationListingHandoffStatus::DependencyUnavailable;
    }

    private function quarantine(
        ModerationOutboxDelivery $delivery,
        ModerationListingHandoffRecord $record,
        string $code,
    ): ModerationListingHandoffStatus {
        $this->results->append($this->withStatus(
            $record,
            ModerationListingHandoffStatus::Quarantined,
            $record->recordedAt,
        ));
        $this->outbox->quarantine($delivery, $code);

        return ModerationListingHandoffStatus::Quarantined;
    }

    private function record(
        ModerationOutboxDelivery $delivery,
        string $decisionId,
        string $commandId,
        string $checksum,
        ModerationListingHandoffStatus $status,
        DateTimeImmutable $at,
    ): ModerationListingHandoffRecord {
        return new ModerationListingHandoffRecord(
            $delivery->message->messageId,
            $delivery->message->event->caseId,
            $decisionId,
            $commandId,
            $checksum,
            $status,
            $at,
        );
    }

    private function emptyRecord(
        ModerationOutboxDelivery $delivery,
        DateTimeImmutable $at,
    ): ModerationListingHandoffRecord {
        return $this->record($delivery, $delivery->message->event->caseId, '', '', ModerationListingHandoffStatus::Quarantined, $at);
    }

    private function withStatus(
        ModerationListingHandoffRecord $record,
        ModerationListingHandoffStatus $status,
        DateTimeImmutable $at,
    ): ModerationListingHandoffRecord {
        return new ModerationListingHandoffRecord(
            $record->messageId,
            $record->caseId,
            $record->decisionId,
            $record->commandId,
            $record->checksum,
            $status,
            $at,
        );
    }
}
