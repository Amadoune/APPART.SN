<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Transport;

use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessage;

interface ExperienceAcceptanceTransportV1
{
    public function serialize(ExperienceAcceptanceOutboxMessage $message): string;

    public function deserialize(string $serialized): ExperienceAcceptanceTransportEnvelope;
}
