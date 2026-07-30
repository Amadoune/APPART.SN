<?php

namespace App\Application\ProfessionalStatusEventRouting;

final readonly class ProfessionalStatusInboxStoreResult
{
    public function __construct(public ProfessionalStatusInboxStoreStatus $status) {}
}
