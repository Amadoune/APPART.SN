<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext;

use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use InvalidArgumentException;

final readonly class AdministrativeActionDecisionAuthority
{
    private function __construct(
        public AdministrativeActionRecordingDisposition $disposition,
        public ActorId $author,
        public ActorId $decisionActor,
    ) {}

    public static function directRecording(ActorId $author, ActorId $decisionActor): self
    {
        if (! $author->equals($decisionActor)) {
            throw new InvalidArgumentException('Direct recording must be performed by the action author.');
        }

        return new self(AdministrativeActionRecordingDisposition::DirectRecording, $author, $decisionActor);
    }

    public static function independentApprovalRequired(ActorId $author, ActorId $decisionActor): self
    {
        if ($author->equals($decisionActor)) {
            throw new InvalidArgumentException('Independent approval requires an actor distinct from the action author.');
        }

        return new self(AdministrativeActionRecordingDisposition::IndependentApprovalRequired, $author, $decisionActor);
    }
}
