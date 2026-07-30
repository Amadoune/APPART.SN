<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusEvent;

final readonly class ProfessionalStatusEventSerializer
{
    public function serialize(ProfessionalStatusEvent $event): string
    {
        return json_encode($event->canonical(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
