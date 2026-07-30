<?php

namespace App\Application\ModerationListingHandoff;

use App\Application\ModerationAtomicOperation\Contract\ModerationAtomicOperationV1;
use App\Application\ModerationAtomicOperation\Contract\ModerationOutboxAppenderV1;
use App\Application\ModerationAtomicOperation\ModerationAtomicDecision;
use App\Application\ModerationAtomicOperation\ModerationAtomicWorkResult;
use App\Application\ModerationAtomicOperation\ModerationOutboxAppendResult;
use App\Application\ModerationEventOutbox\ModerationOutboxDelivery;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationListingHandoff\Contract\ModerationListingHandoffResultStore;
use App\Application\ModerationResidualOperationalAuditContract\ResidualOperationalAuditProductionPolicyV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use DateTimeImmutable;
use Throwable;

final readonly class ModerationListingHandoffTerminalIntegrator
{
    public function __construct(
        private ModerationAtomicOperationV1 $transaction,
        private ModerationListingHandoffResultStore $results,
        private ModerationOutboxAppenderV1 $outbox,
        private ResidualOperationalAuditProductionPolicyV1 $production = new ResidualOperationalAuditProductionPolicyV1,
    ) {}

    public function integrate(
        ModerationOutboxDelivery $source,
        ModerationListingHandoffRecord $requested,
        ModerationListingHandoffStatus $status,
        DateTimeImmutable $recordedAt,
    ): bool {
        if ($this->production->normalizeListingResult($status) === null) {
            return false;
        }

        $work = $this->transaction->execute(function () use ($source, $requested, $recordedAt): ModerationAtomicWorkResult {
            $record = new ModerationListingHandoffRecord(
                $requested->messageId,
                $requested->caseId,
                $requested->decisionId,
                $requested->commandId,
                $requested->checksum,
                ModerationListingHandoffStatus::Applied,
                $recordedAt,
            );
            if (! $this->results->append($record)) {
                return ModerationAtomicWorkResult::rollback(false);
            }

            try {
                $append = $this->outbox->append(new ModerationDeliveryMessageV1(
                    $this->event($source, $requested),
                ));
            } catch (Throwable) {
                return ModerationAtomicWorkResult::rollback(false);
            }
            if (in_array($append, [
                ModerationOutboxAppendResult::DivergentMessage,
                ModerationOutboxAppendResult::Rejected,
            ], true)) {
                return ModerationAtomicWorkResult::rollback(false);
            }

            return ModerationAtomicWorkResult::commit(true);
        });

        return $work->decision === ModerationAtomicDecision::Commit && $work->value === true;
    }

    private function event(
        ModerationOutboxDelivery $source,
        ModerationListingHandoffRecord $requested,
    ): ModerationEventV1 {
        $cause = $source->message->event;

        return new ModerationEventV1(
            ModerationEventTypeV1::TargetActionCompleted,
            $requested->caseId,
            $cause->aggregateVersion,
            [
                'contractVersion' => 'v1',
                'decisionId' => $requested->decisionId,
                'result' => 'applied',
                'targetActionId' => $requested->commandId,
                'targetOwner' => 'Listing',
            ],
            $cause->policyVersion,
            $cause->occurredAt,
            $cause->occurredAt,
            $cause->correlationId,
            $cause->eventId,
        );
    }
}
