<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow;

final readonly class ListingPublicationDecision
{
    private function __construct(
        public ListingPublicationDecisionStatus $status,
        public ?ListingPublicationTransition $transition,
        public ?ListingPublicationDiagnostic $diagnostic,
    ) {}

    public static function allowed(ListingPublicationTransition $transition): self
    {
        return new self(ListingPublicationDecisionStatus::Allowed, $transition, null);
    }

    public static function denied(ListingPublicationDiagnostic $diagnostic): self
    {
        return new self(ListingPublicationDecisionStatus::Denied, null, $diagnostic);
    }
}
