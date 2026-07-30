<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent;

final readonly class LeadLifecycleEventSerializer
{
    public function serialize(LeadLifecycleEvent $event): string
    {
        return json_encode($event->canonical(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
