<?php

namespace App\Application\ProfessionalStatusEventTransport;

interface ProfessionalStatusEventRouter
{
    public function route(ProfessionalStatusTransportEnvelope $envelope): ProfessionalStatusEventRoutingResult;
}
