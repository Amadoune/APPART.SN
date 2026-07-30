<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext;

final readonly class AdministrativeActionDecisionContext
{
    public function __construct(
        public AdministrativeActionDecisionContextVersion $contractVersion,
        public AdministrativeActionReasonEvidence $reasonEvidence,
        public AdministrativeActionDecisionAuthority $authority,
    ) {}

    public function checksum(): AdministrativeActionDecisionContextChecksum
    {
        return AdministrativeActionDecisionContextChecksum::fromString(hash('sha256', implode("\n", [
            (string) $this->contractVersion->value,
            $this->reasonEvidence->value,
            $this->authority->disposition->value,
            $this->authority->author->value,
            $this->authority->decisionActor->value,
        ])));
    }
}
