<?php

namespace App\Application\ModerationHttp;

use App\Application\ModerationHttp\Contract\ModerationHttpRuntimeV1;
use App\Application\ModerationOrchestration\Contract\ClaimModerationQueueItemV1;
use App\Application\ModerationOrchestration\Contract\CloseModerationCaseV1;
use App\Application\ModerationOrchestration\Contract\IssueModerationDecisionV1;
use App\Application\ModerationOrchestration\Contract\ModerationCaseOrchestratorV1;
use App\Application\ModerationOrchestration\Contract\ModerationCommandResult;
use App\Application\ModerationOrchestration\Contract\ModerationCommandStatus;
use App\Application\ModerationOrchestration\Contract\RecordModerationFindingV1;
use App\Application\ModerationOrchestration\Contract\SubmitModerationReportV1;
use App\Application\ModerationOrchestration\Contract\ValidateModerationReportV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\Contract\ModeratorAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModerationCapabilityV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModeratorAuthorizationDecisionV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadModerationCaseV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadModerationDecisionV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadModerationQueueV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadOwnModerationReportV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ModerationCaseViewLevelV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ModerationDecisionPurposeV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationCaseQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationDecisionQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationQueueQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadOwnModerationReportQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadStatusV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadFilterV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadStateV1;
use DateTimeImmutable;

final readonly class DeterministicModerationHttpRuntimeV1 implements ModerationHttpRuntimeV1
{
    public function __construct(
        private ModerationCaseOrchestratorV1 $commands,
        private ReadOwnModerationReportV1 $ownReports,
        private ReadModerationQueueV1 $queue,
        private ReadModerationCaseV1 $cases,
        private ReadModerationDecisionV1 $decisions,
        private ModeratorAuthorizationReaderV1 $authorization,
    ) {}

    public function execute(
        ModerationHttpOperation $operation,
        string $accountId,
        ?string $resourceId,
        ?string $intentId,
        array $input,
    ): ModerationHttpResult {
        $at = new DateTimeImmutable((string) ($input['occurredAt'] ?? 'now'));
        if ($operation->isMutation()) {
            $capability = match ($operation) {
                ModerationHttpOperation::SubmitReport => ModerationCapabilityV1::Report,
                ModerationHttpOperation::ValidateReport => ModerationCapabilityV1::Validate,
                ModerationHttpOperation::RecordFinding,
                ModerationHttpOperation::ClaimQueueItem => ModerationCapabilityV1::Investigate,
                ModerationHttpOperation::IssueDecision,
                ModerationHttpOperation::CloseCase => ModerationCapabilityV1::Decide,
                default => ModerationCapabilityV1::Audit,
            };
            $authorized = $this->authorization->authorize(AccountId::fromString($accountId), $capability, $at);
            if ($authorized !== ModeratorAuthorizationDecisionV1::Allowed) {
                return new ModerationHttpResult(
                    $authorized === ModeratorAuthorizationDecisionV1::DependencyUnavailable
                        ? ModerationHttpStatus::Unavailable
                        : ModerationHttpStatus::Forbidden,
                );
            }
        }

        return match ($operation) {
            ModerationHttpOperation::SubmitReport => $this->command($this->commands->submit(
                new SubmitModerationReportV1(
                    (string) $intentId,
                    (string) $resourceId,
                    $accountId,
                    (string) $input['targetType'],
                    (string) $input['targetId'],
                    (string) $input['category'],
                    (string) $input['statementReference'],
                    $at,
                    (string) $input['policyVersion'],
                ),
            ), true),
            ModerationHttpOperation::ValidateReport => $this->command($this->commands->validate(
                new ValidateModerationReportV1(
                    (string) $intentId,
                    (string) $input['caseId'],
                    (string) $resourceId,
                    $accountId,
                    (string) $input['disposition'],
                    (string) $input['reasonCode'],
                    (int) $input['expectedVersion'],
                    $at,
                    (string) $input['policyVersion'],
                ),
            )),
            ModerationHttpOperation::RecordFinding => $this->command($this->commands->recordFinding(
                new RecordModerationFindingV1(
                    (string) $intentId,
                    (string) $resourceId,
                    (string) $input['findingId'],
                    $accountId,
                    $this->strings($input['reportIds']),
                    (string) $input['findingCode'],
                    $this->strings($input['evidenceReferences']),
                    (int) $input['expectedVersion'],
                    $at,
                    (string) $input['policyVersion'],
                ),
            )),
            ModerationHttpOperation::IssueDecision => $this->command($this->commands->issueDecision(
                new IssueModerationDecisionV1(
                    (string) $intentId,
                    (string) $resourceId,
                    (string) $input['decisionId'],
                    $accountId,
                    $this->strings($input['findingIds']),
                    (string) $input['disposition'],
                    (string) $input['targetAction'],
                    isset($input['supersededDecisionId']) ? (string) $input['supersededDecisionId'] : null,
                    (int) $input['expectedVersion'],
                    $at,
                    (string) $input['policyVersion'],
                ),
            )),
            ModerationHttpOperation::CloseCase => $this->command($this->commands->close(
                new CloseModerationCaseV1(
                    (string) $intentId,
                    (string) $resourceId,
                    $accountId,
                    (string) $input['closureCode'],
                    (int) $input['expectedVersion'],
                    $at,
                    (string) $input['policyVersion'],
                ),
            )),
            ModerationHttpOperation::ClaimQueueItem => $this->command($this->commands->claim(
                new ClaimModerationQueueItemV1(
                    (string) $intentId,
                    (string) $resourceId,
                    $accountId,
                    (string) $input['leaseId'],
                    new DateTimeImmutable((string) $input['leaseExpiresAt']),
                    $at,
                    (string) $input['policyVersion'],
                ),
            )),
            ModerationHttpOperation::ReadOwnReport => $this->read(
                $this->ownReports->read(new ReadOwnModerationReportQueryV1((string) $resourceId, $accountId)),
            ),
            ModerationHttpOperation::ReadQueue => $this->read($this->queue->read(
                new ReadModerationQueueQueryV1(
                    $accountId,
                    $at,
                    new ModerationQueueReadFilterV1(
                        ModerationQueueReadStateV1::from((string) $input['state']),
                        isset($input['category']) ? (string) $input['category'] : null,
                    ),
                    isset($input['cursor']) ? (string) $input['cursor'] : null,
                    (int) $input['limit'],
                ),
            )),
            ModerationHttpOperation::ReadCase => $this->read($this->cases->read(
                new ReadModerationCaseQueryV1(
                    (string) $resourceId,
                    $accountId,
                    $at,
                    ModerationCaseViewLevelV1::from((string) $input['viewLevel']),
                ),
            )),
            ModerationHttpOperation::ReadDecision => $this->read($this->decisions->read(
                new ReadModerationDecisionQueryV1(
                    (string) $resourceId,
                    isset($input['decisionId']) ? (string) $input['decisionId'] : null,
                    $accountId,
                    $at,
                    ModerationDecisionPurposeV1::from((string) $input['purpose']),
                ),
            )),
        };
    }

    private function command(ModerationCommandResult $result, bool $created = false): ModerationHttpResult
    {
        $status = match ($result->status) {
            ModerationCommandStatus::Applied => $created ? ModerationHttpStatus::Created : ModerationHttpStatus::Succeeded,
            ModerationCommandStatus::AlreadyApplied, ModerationCommandStatus::Closed => ModerationHttpStatus::Succeeded,
            ModerationCommandStatus::ForbiddenActor, ModerationCommandStatus::FourEyesViolation => ModerationHttpStatus::Forbidden,
            ModerationCommandStatus::CaseMissing, ModerationCommandStatus::ReportMissing,
            ModerationCommandStatus::FindingMissing, ModerationCommandStatus::ItemMissing => ModerationHttpStatus::NotFound,
            ModerationCommandStatus::DependencyUnavailable => ModerationHttpStatus::Unavailable,
            ModerationCommandStatus::DivergentIntent, ModerationCommandStatus::VersionConflict,
            ModerationCommandStatus::LeaseConflict, ModerationCommandStatus::InvalidSupersession,
            ModerationCommandStatus::NotClosable => ModerationHttpStatus::Conflict,
            default => ModerationHttpStatus::Invalid,
        };

        return new ModerationHttpResult($status, array_filter([
            'caseId' => $result->caseId,
            'version' => $result->version,
        ], static fn (mixed $value): bool => $value !== null));
    }

    private function read(object $result): ModerationHttpResult
    {
        /** @var ReadStatusV1 $readStatus */
        $readStatus = $result->status;
        $status = match ($readStatus) {
            ReadStatusV1::Visible, ReadStatusV1::Available, ReadStatusV1::Found => ModerationHttpStatus::Succeeded,
            ReadStatusV1::NotVisible, ReadStatusV1::Missing, ReadStatusV1::Empty => ModerationHttpStatus::NotFound,
            ReadStatusV1::ForbiddenActor => ModerationHttpStatus::Forbidden,
            ReadStatusV1::InvalidCursor, ReadStatusV1::Corrupted => ModerationHttpStatus::Invalid,
            ReadStatusV1::QueueUnavailable, ReadStatusV1::DependencyUnavailable => ModerationHttpStatus::Unavailable,
        };

        $data = [];
        foreach (['report', 'page', 'case', 'decision'] as $property) {
            if (property_exists($result, $property) && $result->{$property} !== null) {
                $data[$property] = $result->{$property};
            }
        }

        return new ModerationHttpResult($status, $data);
    }

    /** @return list<string> */
    private function strings(mixed $values): array
    {
        return is_array($values) ? array_values(array_map('strval', $values)) : [];
    }
}
