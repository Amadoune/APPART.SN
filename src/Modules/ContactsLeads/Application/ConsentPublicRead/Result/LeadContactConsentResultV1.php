<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Result;

final readonly class LeadContactConsentResultV1
{
    public function __construct(public LeadContactConsentStatusV1 $status) {}
}
