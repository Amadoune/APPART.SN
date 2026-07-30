<?php

namespace App\Application\ModerationOrchestration;

use App\Application\ModerationOrchestration\Contract\ClaimModerationQueueItemV1;
use App\Application\ModerationOrchestration\Contract\CloseModerationCaseV1;
use App\Application\ModerationOrchestration\Contract\IssueModerationDecisionV1;
use App\Application\ModerationOrchestration\Contract\ModerationCaseOrchestratorV1;
use App\Application\ModerationOrchestration\Contract\ModerationCommandResult;
use App\Application\ModerationOrchestration\Contract\ModerationCommandStatus;
use App\Application\ModerationOrchestration\Contract\RecordModerationFindingV1;
use App\Application\ModerationOrchestration\Contract\SubmitModerationReportV1;
use App\Application\ModerationOrchestration\Contract\ValidateModerationReportV1;
use App\Application\ModerationRuntime\Contract\ModerationRuntimeV1;
use App\Application\ModerationRuntime\ModerationRuntimeStatus;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationCasePersistenceState;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceReadStatus;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceRecord;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceWriteResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueClaimResult;
use DateTimeImmutable;

final readonly class DeterministicModerationCaseOrchestratorV1 implements ModerationCaseOrchestratorV1
{
    public function __construct(private ModerationRuntimeV1 $runtime) {}

    public function submit(SubmitModerationReportV1 $command): ModerationCommandResult
    {
        if (! $this->available()) {
            return $this->result(ModerationCommandStatus::DependencyUnavailable);
        }
        $caseId = CanonicalModerationCommand::deterministicUuid('moderation-case-v1', $command->reportId);
        $candidate = new ModerationCasePersistenceState(
            $caseId, $command->targetType, $command->targetId, 'Open', null, 1,
            $command->intentId, CanonicalModerationCommand::checksum($command), $command->occurredAt,
            [new ModerationPersistenceRecord($command->reportId, [
                'actorAccountId' => $command->actorAccountId,
                'category' => $command->category,
                'disposition' => null,
                'policyVersion' => $command->policyVersion,
                'reasonCode' => null,
                'statementReference' => $command->statementReference,
                'validationActorId' => null,
            ], $command->occurredAt)],
            [], [],
        );

        return $this->write($this->runtime->cases()->save($candidate, 0), $caseId, 1);
    }

    public function validate(ValidateModerationReportV1 $command): ModerationCommandResult
    {
        $state = $this->state($command->caseId);
        if ($state instanceof ModerationCommandResult) {
            return $state;
        }
        if (($replay = $this->replay($state, $command)) !== null) {
            return $replay;
        }
        $report = $this->record($state->reports, $command->reportId);
        if ($report === null) {
            return $this->result(ModerationCommandStatus::ReportMissing, $state);
        }
        if (($report->payload['actorAccountId'] ?? null) === $command->actorAccountId) {
            return $this->result(ModerationCommandStatus::ForbiddenActor, $state);
        }
        if ($state->status === 'Closed') {
            return $this->result(ModerationCommandStatus::Closed, $state);
        }
        $reports = $this->replace($state->reports, new ModerationPersistenceRecord(
            $report->id,
            [...$report->payload, 'disposition' => $command->disposition, 'reasonCode' => $command->reasonCode, 'validationActorId' => $command->actorAccountId],
            $command->occurredAt,
        ));

        return $this->save($state, $command, $command->expectedVersion, $command->occurredAt, reports: $reports);
    }

    public function recordFinding(RecordModerationFindingV1 $command): ModerationCommandResult
    {
        $state = $this->state($command->caseId);
        if ($state instanceof ModerationCommandResult) {
            return $state;
        }
        if (($replay = $this->replay($state, $command)) !== null) {
            return $replay;
        }
        if ($state->status === 'Closed') {
            return $this->result(ModerationCommandStatus::Closed, $state);
        }
        if ($command->reportIds === [] || $command->evidenceReferences === []) {
            return $this->result(ModerationCommandStatus::InsufficientEvidence, $state);
        }
        $sameValidator = 0;
        foreach ($command->reportIds as $reportId) {
            $report = $this->record($state->reports, $reportId);
            if ($report === null) {
                return $this->result(ModerationCommandStatus::ReportMissing, $state);
            }
            if (($report->payload['disposition'] ?? null) !== 'Accepted') {
                return $this->result(ModerationCommandStatus::ReportNotAccepted, $state);
            }
            if (($report->payload['validationActorId'] ?? null) === $command->actorAccountId) {
                $sameValidator++;
            }
        }
        if ($sameValidator === count($command->reportIds)) {
            return $this->result(ModerationCommandStatus::ForbiddenActor, $state);
        }
        $finding = new ModerationPersistenceRecord($command->findingId, [
            'actorAccountId' => $command->actorAccountId,
            'evidenceReferences' => $command->evidenceReferences,
            'findingCode' => $command->findingCode,
            'policyVersion' => $command->policyVersion,
            'reportIds' => $command->reportIds,
        ], $command->occurredAt);

        return $this->save($state, $command, $command->expectedVersion, $command->occurredAt, findings: [...$state->findings, $finding]);
    }

    public function issueDecision(IssueModerationDecisionV1 $command): ModerationCommandResult
    {
        $state = $this->state($command->caseId);
        if ($state instanceof ModerationCommandResult) {
            return $state;
        }
        if (($replay = $this->replay($state, $command)) !== null) {
            return $replay;
        }
        if ($state->status === 'Closed') {
            return $this->result(ModerationCommandStatus::Closed, $state);
        }
        if ($command->findingIds === []) {
            return $this->result(ModerationCommandStatus::InsufficientEvidence, $state);
        }
        if ($state->currentDecisionId !== $command->supersededDecisionId) {
            return $this->result(ModerationCommandStatus::InvalidSupersession, $state);
        }
        foreach ($state->reports as $report) {
            if (($report->payload['actorAccountId'] ?? null) === $command->actorAccountId
                || ($report->payload['validationActorId'] ?? null) === $command->actorAccountId) {
                return $this->result(ModerationCommandStatus::FourEyesViolation, $state);
            }
        }
        foreach ($state->findings as $finding) {
            if (($finding->payload['actorAccountId'] ?? null) === $command->actorAccountId) {
                return $this->result(ModerationCommandStatus::FourEyesViolation, $state);
            }
        }
        foreach ($command->findingIds as $findingId) {
            if ($this->record($state->findings, $findingId) === null) {
                return $this->result(ModerationCommandStatus::FindingMissing, $state);
            }
        }
        $decision = new ModerationPersistenceRecord($command->decisionId, [
            'actorAccountId' => $command->actorAccountId,
            'disposition' => $command->disposition,
            'findingIds' => $command->findingIds,
            'policyVersion' => $command->policyVersion,
            'supersededDecisionId' => $command->supersededDecisionId,
            'targetAction' => $command->targetAction,
        ], $command->occurredAt);

        return $this->save(
            $state, $command, $command->expectedVersion, $command->occurredAt,
            status: 'Decided', currentDecisionId: $command->decisionId,
            decisions: [...$state->decisions, $decision],
        );
    }

    public function close(CloseModerationCaseV1 $command): ModerationCommandResult
    {
        $state = $this->state($command->caseId);
        if ($state instanceof ModerationCommandResult) {
            return $state;
        }
        if (($replay = $this->replay($state, $command)) !== null) {
            return $replay;
        }
        if ($state->status === 'Closed') {
            return $this->result(ModerationCommandStatus::Closed, $state);
        }
        if ($state->currentDecisionId === null) {
            return $this->result(ModerationCommandStatus::NotClosable, $state);
        }

        return $this->save($state, $command, $command->expectedVersion, $command->occurredAt, status: 'Closed');
    }

    public function claim(ClaimModerationQueueItemV1 $command): ModerationCommandResult
    {
        if (! $this->available()) {
            return $this->result(ModerationCommandStatus::DependencyUnavailable);
        }
        $result = $this->runtime->queue()->claim(
            $command->queueItemId, $command->leaseId, $command->actorAccountId,
            $command->leaseExpiresAt, $command->occurredAt, $command->intentId,
            CanonicalModerationCommand::checksum($command),
        );

        return $this->result(match ($result) {
            ModerationQueueClaimResult::Applied => ModerationCommandStatus::Applied,
            ModerationQueueClaimResult::AlreadyApplied => ModerationCommandStatus::AlreadyApplied,
            ModerationQueueClaimResult::DivergentIntent => ModerationCommandStatus::DivergentIntent,
            ModerationQueueClaimResult::VersionConflict => ModerationCommandStatus::VersionConflict,
            ModerationQueueClaimResult::Missing => ModerationCommandStatus::ItemMissing,
            ModerationQueueClaimResult::LeaseConflict => ModerationCommandStatus::LeaseConflict,
            ModerationQueueClaimResult::Rejected => ModerationCommandStatus::Rejected,
            ModerationQueueClaimResult::Claimed => ModerationCommandStatus::Applied,
            ModerationQueueClaimResult::AlreadyClaimed => ModerationCommandStatus::AlreadyApplied,
        });
    }

    private function available(): bool
    {
        return $this->runtime->inspect()->status === ModerationRuntimeStatus::Healthy;
    }

    private function state(string $caseId): ModerationCasePersistenceState|ModerationCommandResult
    {
        if (! $this->available()) {
            return $this->result(ModerationCommandStatus::DependencyUnavailable);
        }
        $read = $this->runtime->cases()->read($caseId);

        return match ($read->status) {
            ModerationPersistenceReadStatus::Found => $read->state ?? $this->result(ModerationCommandStatus::DependencyUnavailable),
            ModerationPersistenceReadStatus::Missing => $this->result(ModerationCommandStatus::CaseMissing),
            default => $this->result(ModerationCommandStatus::DependencyUnavailable),
        };
    }

    /**
     * @param  list<ModerationPersistenceRecord>|null  $reports
     * @param  list<ModerationPersistenceRecord>|null  $findings
     * @param  list<ModerationPersistenceRecord>|null  $decisions
     */
    private function save(
        ModerationCasePersistenceState $state, object $command, int $expectedVersion,
        DateTimeImmutable $occurredAt, ?string $status = null, ?string $currentDecisionId = null,
        ?array $reports = null, ?array $findings = null, ?array $decisions = null,
    ): ModerationCommandResult {
        $intentId = get_object_vars($command)['intentId'] ?? null;
        if (! is_string($intentId)) {
            return $this->result(ModerationCommandStatus::Rejected, $state);
        }
        $candidate = new ModerationCasePersistenceState(
            $state->caseId, $state->targetType, $state->targetId, $status ?? $state->status,
            $currentDecisionId ?? $state->currentDecisionId, $expectedVersion + 1,
            $intentId, CanonicalModerationCommand::checksum($command), $occurredAt,
            $reports ?? $state->reports, $findings ?? $state->findings, $decisions ?? $state->decisions,
        );

        return $this->write($this->runtime->cases()->save($candidate, $expectedVersion), $state->caseId, $candidate->version);
    }

    private function write(ModerationPersistenceWriteResult $write, string $caseId, int $version): ModerationCommandResult
    {
        return new ModerationCommandResult(match ($write) {
            ModerationPersistenceWriteResult::Applied => ModerationCommandStatus::Applied,
            ModerationPersistenceWriteResult::AlreadyApplied => ModerationCommandStatus::AlreadyApplied,
            ModerationPersistenceWriteResult::DivergentIntent => ModerationCommandStatus::DivergentIntent,
            ModerationPersistenceWriteResult::VersionConflict => ModerationCommandStatus::VersionConflict,
            default => ModerationCommandStatus::Rejected,
        }, $caseId, $version);
    }

    private function result(ModerationCommandStatus $status, ?ModerationCasePersistenceState $state = null): ModerationCommandResult
    {
        return new ModerationCommandResult($status, $state?->caseId, $state?->version);
    }

    private function replay(ModerationCasePersistenceState $state, object $command): ?ModerationCommandResult
    {
        $intentId = get_object_vars($command)['intentId'] ?? null;
        if (! is_string($intentId) || $state->intentId !== $intentId) {
            return null;
        }

        return $this->result(
            hash_equals($state->intentChecksum, CanonicalModerationCommand::checksum($command))
                ? ModerationCommandStatus::AlreadyApplied
                : ModerationCommandStatus::DivergentIntent,
            $state,
        );
    }

    /** @param list<ModerationPersistenceRecord> $records */
    private function record(array $records, string $id): ?ModerationPersistenceRecord
    {
        foreach ($records as $record) {
            if ($record->id === $id) {
                return $record;
            }
        }

        return null;
    }

    /**
     * @param  list<ModerationPersistenceRecord>  $records
     * @return list<ModerationPersistenceRecord>
     */
    private function replace(array $records, ModerationPersistenceRecord $replacement): array
    {
        return array_map(
            static fn (ModerationPersistenceRecord $record): ModerationPersistenceRecord => $record->id === $replacement->id ? $replacement : $record,
            $records,
        );
    }
}
