<?php

namespace Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Result;

final readonly class LeadIngressAntiAbuseResultV1
{
    public function __construct(public LeadIngressAntiAbuseStatusV1 $status) {}
}
