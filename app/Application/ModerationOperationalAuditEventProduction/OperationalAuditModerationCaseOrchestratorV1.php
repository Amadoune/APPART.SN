<?php

namespace App\Application\ModerationOperationalAuditEventProduction;

use App\Application\ModerationAtomicOperation\Contract\ModerationAtomicOperationV1;
use App\Application\ModerationAtomicOperation\Contract\ModerationOutboxAppenderV1;
use App\Application\ModerationAtomicOperation\ModerationAtomicWorkResult;
use App\Application\ModerationAtomicOperation\ModerationOutboxAppendResult;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationOperationalAuditEventProduction\Contract\ModerationOperationalAuditOutboxAppenderV1;
use App\Application\ModerationOrchestration\Contract\ClaimModerationQueueItemV1;
use App\Application\ModerationOrchestration\Contract\CloseModerationCaseV1;
use App\Application\ModerationOrchestration\Contract\IssueModerationDecisionV1;
use App\Application\ModerationOrchestration\Contract\ModerationCaseOrchestratorV1;
use App\Application\ModerationOrchestration\Contract\ModerationCommandResult;
use App\Application\ModerationOrchestration\Contract\ModerationCommandStatus;
use App\Application\ModerationOrchestration\Contract\RecordModerationFindingV1;
use App\Application\ModerationOrchestration\Contract\SubmitModerationReportV1;
use App\Application\ModerationOrchestration\Contract\ValidateModerationReportV1;
use App\Application\ModerationOrchestration\DeterministicModerationCaseOrchestratorV1;
use App\Application\ModerationRuntime\Contract\ModerationRuntimeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\FindingRecordedEventV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\QueueItemClaimedEventV1;

final readonly class OperationalAuditModerationCaseOrchestratorV1 implements ModerationCaseOrchestratorV1
{
    public function __construct(
        private DeterministicModerationCaseOrchestratorV1 $inner,
        private ModerationRuntimeV1 $runtime,
        private ModerationAtomicOperationV1 $transaction,
        private ModerationOperationalAuditOutboxAppenderV1 $outbox,
        private ModerationOutboxAppenderV1 $historicalOutbox,
    ) {}

    public function submit(SubmitModerationReportV1 $command): ModerationCommandResult
    {
        return $this->atomic(
            fn (): ModerationCommandResult => $this->inner->submit($command),
            static fn (ModerationCommandResult $result): ModerationEventV1 => new ModerationEventV1(
                ModerationEventTypeV1::ReportSubmitted,
                (string) $result->caseId,
                (int) $result->version,
                [
                    'category' => $command->category,
                    'reportId' => $command->reportId,
                    'targetId' => $command->targetId,
                    'targetType' => $command->targetType,
                ],
                $command->policyVersion,
                $command->occurredAt,
                $command->occurredAt,
                $command->intentId,
                $command->intentId,
            ),
        );
    }

    public function validate(ValidateModerationReportV1 $command): ModerationCommandResult
    {
        return $this->atomic(
            fn (): ModerationCommandResult => $this->inner->validate($command),
            static fn (ModerationCommandResult $result): ModerationEventV1 => new ModerationEventV1(
                ModerationEventTypeV1::ReportValidated,
                $command->caseId,
                (int) $result->version,
                ['disposition' => $command->disposition, 'reportId' => $command->reportId],
                $command->policyVersion,
                $command->occurredAt,
                $command->occurredAt,
                $command->intentId,
                $command->intentId,
            ),
        );
    }

    public function recordFinding(RecordModerationFindingV1 $command): ModerationCommandResult
    {
        return $this->atomic(
            fn (): ModerationCommandResult => $this->inner->recordFinding($command),
            static fn (ModerationCommandResult $result): FindingRecordedEventV1 => new FindingRecordedEventV1(
                $command->caseId,
                $command->findingId,
                (int) $result->version,
                $command->policyVersion,
                $command->occurredAt,
                $command->occurredAt,
                $command->intentId,
                $command->intentId,
            ),
        );
    }

    public function issueDecision(IssueModerationDecisionV1 $command): ModerationCommandResult
    {
        return $this->atomicHistorical(
            fn (): ModerationCommandResult => $this->inner->issueDecision($command),
            static fn (ModerationCommandResult $result): ModerationEventV1 => new ModerationEventV1(
                ModerationEventTypeV1::DecisionIssued,
                $command->caseId,
                (int) $result->version,
                [
                    'decisionId' => $command->decisionId,
                    'disposition' => $command->disposition,
                    'supersededDecisionId' => $command->supersededDecisionId,
                    'targetAction' => $command->targetAction,
                ],
                $command->policyVersion,
                $command->occurredAt,
                $command->occurredAt,
                $command->intentId,
                $command->intentId,
            ),
        );
    }

    public function close(CloseModerationCaseV1 $command): ModerationCommandResult
    {
        return $this->atomicHistorical(
            fn (): ModerationCommandResult => $this->inner->close($command),
            function (ModerationCommandResult $result) use ($command): ModerationEventV1 {
                $case = $this->runtime->cases()->read($command->caseId);
                if ($case->state === null || $case->state->currentDecisionId === null) {
                    throw new \UnexpectedValueException('Applied close has no current owner decision.');
                }

                return new ModerationEventV1(
                    ModerationEventTypeV1::CaseClosed,
                    $command->caseId,
                    (int) $result->version,
                    [
                        'closureCode' => $command->closureCode,
                        'currentDecisionId' => $case->state->currentDecisionId,
                    ],
                    $command->policyVersion,
                    $command->occurredAt,
                    $command->occurredAt,
                    $command->intentId,
                    $command->intentId,
                );
            },
        );
    }

    public function claim(ClaimModerationQueueItemV1 $command): ModerationCommandResult
    {
        return $this->atomic(
            fn (): ModerationCommandResult => $this->inner->claim($command),
            function () use ($command): QueueItemClaimedEventV1 {
                $item = $this->runtime->queue()->read($command->queueItemId);
                if ($item === null) {
                    throw new \UnexpectedValueException('Applied Queue claim has no owner state.');
                }

                return new QueueItemClaimedEventV1(
                    $item->caseId,
                    $command->queueItemId,
                    $item->sourceVersion,
                    $command->policyVersion,
                    $command->occurredAt,
                    $command->occurredAt,
                    $command->intentId,
                    $command->intentId,
                );
            },
        );
    }

    /**
     * @param  callable(): ModerationCommandResult  $mutation
     * @param  callable(ModerationCommandResult): object  $event
     */
    private function atomic(callable $mutation, callable $event): ModerationCommandResult
    {
        $work = $this->transaction->execute(function () use ($mutation, $event): ModerationAtomicWorkResult {
            $result = $mutation();
            if ($result->status !== ModerationCommandStatus::Applied) {
                return ModerationAtomicWorkResult::commit($result);
            }
            try {
                $append = $this->outbox->append(
                    new ModerationOperationalAuditOutboxMessageV1($event($result)),
                );
            } catch (\Throwable) {
                return ModerationAtomicWorkResult::rollback($this->rejected($result));
            }
            if (in_array(
                $append,
                [ModerationOutboxAppendResult::DivergentMessage, ModerationOutboxAppendResult::Rejected],
                true,
            )) {
                return ModerationAtomicWorkResult::rollback($this->rejected($result));
            }

            return ModerationAtomicWorkResult::commit($result);
        });

        return $work->value instanceof ModerationCommandResult
            ? $work->value
            : new ModerationCommandResult(ModerationCommandStatus::Rejected);
    }

    /**
     * @param  callable(): ModerationCommandResult  $mutation
     * @param  callable(ModerationCommandResult): ModerationEventV1  $event
     */
    private function atomicHistorical(callable $mutation, callable $event): ModerationCommandResult
    {
        $work = $this->transaction->execute(function () use ($mutation, $event): ModerationAtomicWorkResult {
            $result = $mutation();
            if ($result->status !== ModerationCommandStatus::Applied) {
                return ModerationAtomicWorkResult::commit($result);
            }
            try {
                $append = $this->historicalOutbox->append(
                    new ModerationDeliveryMessageV1($event($result)),
                );
            } catch (\Throwable) {
                return ModerationAtomicWorkResult::rollback($this->rejected($result));
            }
            if (in_array(
                $append,
                [ModerationOutboxAppendResult::DivergentMessage, ModerationOutboxAppendResult::Rejected],
                true,
            )) {
                return ModerationAtomicWorkResult::rollback($this->rejected($result));
            }

            return ModerationAtomicWorkResult::commit($result);
        });

        return $work->value instanceof ModerationCommandResult
            ? $work->value
            : new ModerationCommandResult(ModerationCommandStatus::Rejected);
    }

    private function rejected(ModerationCommandResult $result): ModerationCommandResult
    {
        return new ModerationCommandResult(
            ModerationCommandStatus::Rejected,
            $result->caseId,
            $result->version,
        );
    }
}
