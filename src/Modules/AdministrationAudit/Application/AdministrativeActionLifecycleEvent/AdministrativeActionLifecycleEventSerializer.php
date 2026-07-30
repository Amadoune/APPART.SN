<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent;

final readonly class AdministrativeActionLifecycleEventSerializer
{
    public function serialize(AdministrativeActionLifecycleEvent $event): string
    {
        return json_encode(
            $event->canonical(),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }
}
