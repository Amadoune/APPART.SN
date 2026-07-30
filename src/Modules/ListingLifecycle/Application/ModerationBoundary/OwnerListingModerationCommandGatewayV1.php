<?php

namespace Appart\Modules\ListingLifecycle\Application\ModerationBoundary;

use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\Contract\ListingModerationCommandGatewayV1;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\Contract\ListingModerationIntentStore;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\Contract\ListingModerationIntentTransaction;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\ListingModerationIntentReservationV1;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationDiagnosticCode;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceReadStatus;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

final readonly class OwnerListingModerationCommandGatewayV1 implements ListingModerationCommandGatewayV1
{
    public function __construct(
        private ListingModerationIntentStore $intentStore,
        private ListingModerationIntentTransaction $transaction,
        private ListingPublicationOrchestrator $publication,
        private ListingPublicationWorkflowStore $workflowStore,
    ) {}

    public function apply(
        ListingId $listingId,
        ListingModerationActionV1 $action,
        string $commandId,
        string $checksum,
        DateTimeImmutable $occurredAt,
    ): ListingModerationCommandResultV1 {
        try {
            return $this->transaction->run(function () use (
                $listingId,
                $action,
                $commandId,
                $checksum,
                $occurredAt,
            ): ListingModerationCommandResultV1 {
                $current = $this->workflowStore->read($listingId);
                if ($current->status !== ListingPublicationPersistenceReadStatus::Found || $current->snapshot === null) {
                    return $current->status === ListingPublicationPersistenceReadStatus::Missing
                        ? ListingModerationCommandResultV1::Rejected
                        : ListingModerationCommandResultV1::DependencyUnavailable;
                }
                $expectedVersion = $current->snapshot->version;

                $reservation = $this->intentStore->reserve($commandId, $checksum, $occurredAt);
                if ($reservation === ListingModerationIntentReservationV1::AlreadyApplied) {
                    return ListingModerationCommandResultV1::AlreadyApplied;
                }
                if ($reservation === ListingModerationIntentReservationV1::DivergentIntent) {
                    return ListingModerationCommandResultV1::DivergentIntent;
                }

                $result = self::mapResult($this->publication->transition(
                    new ListingPublicationOrchestrationRequest(
                        $listingId,
                        ListingPublicationAction::from($action->value),
                        $expectedVersion,
                    ),
                ));
                if (! $this->intentStore->complete($commandId, $checksum, $result, $occurredAt)) {
                    throw new RuntimeException('Unable to complete the Listing moderation intent.');
                }

                return $result;
            });
        } catch (Throwable) {
            return ListingModerationCommandResultV1::DependencyUnavailable;
        }
    }

    private static function mapResult(
        ListingPublicationOrchestrationResult $result,
    ): ListingModerationCommandResultV1 {
        return match ($result->status) {
            ListingPublicationOrchestrationStatus::Applied => ListingModerationCommandResultV1::Applied,
            ListingPublicationOrchestrationStatus::AlreadyApplied => ListingModerationCommandResultV1::AlreadyApplied,
            ListingPublicationOrchestrationStatus::Denied => ListingModerationCommandResultV1::Rejected,
            ListingPublicationOrchestrationStatus::PersistenceFailure => ListingModerationCommandResultV1::DependencyUnavailable,
            ListingPublicationOrchestrationStatus::ConcurrencyConflict => $result->orchestrationDiagnostic
                === ListingPublicationOrchestrationDiagnosticCode::VersionConflict
                    ? ListingModerationCommandResultV1::VersionConflict
                    : ListingModerationCommandResultV1::Rejected,
        };
    }
}
