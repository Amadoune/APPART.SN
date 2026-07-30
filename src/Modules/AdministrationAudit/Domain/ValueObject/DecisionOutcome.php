<?php

namespace Appart\Modules\AdministrationAudit\Domain\ValueObject;

enum DecisionOutcome: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
}
