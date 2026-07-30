<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

enum ContactSubject: string
{
    case GeneralInquiry = 'general_inquiry';
    case VisitRequest = 'visit_request';
    case AvailabilityRequest = 'availability_request';
}
