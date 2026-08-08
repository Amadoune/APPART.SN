<?php

namespace Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Result;

use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressId;
use LogicException;

final readonly class LeadIngressSubmissionResultV1
{
    private function __construct(
        public LeadIngressSubmissionStatusV1 $status,
        public ?LeadIngressId $leadIngressId = null,
    ) {
        $exposesId = in_array($status, [
            LeadIngressSubmissionStatusV1::Accepted,
            LeadIngressSubmissionStatusV1::AlreadyAccepted,
        ], true);

        if ($exposesId !== ($leadIngressId !== null)) {
            throw new LogicException('Only accepted lead ingress results may expose an identifier.');
        }
    }

    public static function accepted(LeadIngressId $leadIngressId): self
    {
        return new self(LeadIngressSubmissionStatusV1::Accepted, $leadIngressId);
    }

    public static function alreadyAccepted(LeadIngressId $leadIngressId): self
    {
        return new self(LeadIngressSubmissionStatusV1::AlreadyAccepted, $leadIngressId);
    }

    public static function closed(LeadIngressSubmissionStatusV1 $status): self
    {
        return new self($status);
    }
}
