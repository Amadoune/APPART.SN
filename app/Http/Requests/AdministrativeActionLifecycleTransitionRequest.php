<?php

namespace App\Http\Requests;

use App\Application\AdministrativeActionLifecycleEventIntegration\AdministrativeActionLifecycleAtomicEventRequest;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionAuthority;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContext;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContextVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionReasonEvidence;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionRecordingDisposition;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionExpectedVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionTransitionExecutionContext;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AdministrativeActionLifecycleTransitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $action = (string) $this->input('action');
        $disposition = (string) $this->input('recordingDisposition');
        $recording = $action === AdministrativeActionLifecycleAction::Record->value;
        $approval = $action === AdministrativeActionLifecycleAction::Approve->value;
        $decision = in_array($action, [
            AdministrativeActionLifecycleAction::Approve->value,
            AdministrativeActionLifecycleAction::Reject->value,
        ], true);
        $direct = $disposition === AdministrativeActionRecordingDisposition::DirectRecording->value;

        return [
            'action' => ['bail', 'required', 'string', Rule::in([
                AdministrativeActionLifecycleAction::Record->value,
                AdministrativeActionLifecycleAction::Approve->value,
                AdministrativeActionLifecycleAction::Reject->value,
            ])],
            'contextVersion' => ['bail', 'required', 'integer', Rule::in([
                AdministrativeActionDecisionContextVersion::V1->value,
            ])],
            'expectedVersion' => ['bail', 'required', 'integer', 'min:1'],
            'actorId' => ['bail', 'required', 'string', 'regex:/^[A-Za-z0-9][A-Za-z0-9._:-]{2,127}$/', $recording ? 'same:authorId' : 'same:decisionActorId'],
            'occurredAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/'],
            'recordedAt' => ['bail', 'required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/', 'after_or_equal:occurredAt'],
            'historicalReason' => ['bail', 'required', 'string', 'min:10', 'max:1000'],
            'reasonEvidence' => ['bail', 'required', 'string', Rule::in([
                AdministrativeActionReasonEvidence::Present->value,
                AdministrativeActionReasonEvidence::Missing->value,
            ])],
            'recordingDisposition' => ['bail', 'required', 'string', Rule::in(
                $recording
                    ? [
                        AdministrativeActionRecordingDisposition::DirectRecording->value,
                        AdministrativeActionRecordingDisposition::IndependentApprovalRequired->value,
                    ]
                    : [AdministrativeActionRecordingDisposition::IndependentApprovalRequired->value],
            )],
            'authorId' => ['bail', 'required', 'string', 'regex:/^[A-Za-z0-9][A-Za-z0-9._:-]{2,127}$/'],
            'decisionActorId' => [
                'bail',
                'required',
                'string',
                'regex:/^[A-Za-z0-9][A-Za-z0-9._:-]{2,127}$/',
                $direct ? 'same:authorId' : 'different:authorId',
            ],
            'approvalId' => [
                'bail',
                Rule::requiredIf($approval),
                $approval ? 'uuid' : 'prohibited',
            ],
            'decisionId' => [
                'bail',
                Rule::requiredIf($decision),
                $decision ? 'uuid' : 'prohibited',
            ],
        ];
    }

    public function applicationRequest(): AdministrativeActionLifecycleAtomicEventRequest
    {
        /** @var array{action:string,contextVersion:int,expectedVersion:int,actorId:string,occurredAt:string,recordedAt:string,historicalReason:string,reasonEvidence:string,recordingDisposition:string,authorId:string,decisionActorId:string,approvalId?:string,decisionId?:string} $input */
        $input = $this->validated();
        $action = AdministrativeActionLifecycleAction::from($input['action']);
        $author = ActorId::fromString($input['authorId']);
        $decisionActor = ActorId::fromString($input['decisionActorId']);
        $authority = $input['recordingDisposition'] === AdministrativeActionRecordingDisposition::DirectRecording->value
            ? AdministrativeActionDecisionAuthority::directRecording($author, $decisionActor)
            : AdministrativeActionDecisionAuthority::independentApprovalRequired($author, $decisionActor);
        $decisionContext = new AdministrativeActionDecisionContext(
            AdministrativeActionDecisionContextVersion::from($input['contextVersion']),
            AdministrativeActionReasonEvidence::from($input['reasonEvidence']),
            $authority,
        );
        $expectedVersion = new AdministrativeActionExpectedVersion($input['expectedVersion']);
        $actor = ActorId::fromString($input['actorId']);
        $occurredAt = AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(
            new DateTimeImmutable($input['occurredAt']),
        );
        $reason = AuditReason::fromString($input['historicalReason']);
        $context = match ($action) {
            AdministrativeActionLifecycleAction::Record => AdministrativeActionTransitionExecutionContext::record(
                $expectedVersion,
                $actor,
                $occurredAt,
                $reason,
                $decisionContext,
            ),
            AdministrativeActionLifecycleAction::Approve => AdministrativeActionTransitionExecutionContext::approve(
                $expectedVersion,
                $actor,
                $occurredAt,
                ApprovalId::fromString((string) $input['approvalId']),
                DecisionId::fromString((string) $input['decisionId']),
                $reason,
                $decisionContext,
            ),
            AdministrativeActionLifecycleAction::Reject => AdministrativeActionTransitionExecutionContext::reject(
                $expectedVersion,
                $actor,
                $occurredAt,
                DecisionId::fromString((string) $input['decisionId']),
                $reason,
                $decisionContext,
            ),
            AdministrativeActionLifecycleAction::Unknown => throw new \LogicException('Unknown Administrative Action Lifecycle action passed HTTP validation.'),
        };

        return new AdministrativeActionLifecycleAtomicEventRequest(
            AdministrativeActionId::fromString((string) $this->route('actionId')),
            $action,
            $context,
            AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(
                new DateTimeImmutable($input['recordedAt']),
            ),
        );
    }
}
