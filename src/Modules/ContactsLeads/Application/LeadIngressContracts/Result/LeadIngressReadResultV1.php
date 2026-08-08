<?php

namespace Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Result;

use LogicException;

final readonly class LeadIngressReadResultV1
{
    private function __construct(
        public LeadIngressReadStatusV1 $status,
        public ?LeadIngressReceiptV1 $receipt = null,
    ) {
        if (($status === LeadIngressReadStatusV1::Found) !== ($receipt !== null)) {
            throw new LogicException('Only a found lead ingress may expose a receipt.');
        }
    }

    public static function found(LeadIngressReceiptV1 $receipt): self
    {
        return new self(LeadIngressReadStatusV1::Found, $receipt);
    }

    public static function closed(LeadIngressReadStatusV1 $status): self
    {
        return new self($status);
    }
}
