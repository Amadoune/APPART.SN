<?php

namespace App\Application\ProfessionalStatusEventRouting;

use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusTransportEnvelope;

interface ProfessionalStatusInboxStore
{
    public function store(ProfessionalStatusTransportEnvelope $envelope): ProfessionalStatusInboxStoreResult;
}
