<?php

namespace Appart\Modules\Professionals\Application\ProfessionalLeadRecipientPublicRead\Contract;

use Appart\Modules\Professionals\Application\ProfessionalLeadRecipientPublicRead\LeadRecipientObservedAt;
use Appart\Modules\Professionals\Application\ProfessionalLeadRecipientPublicRead\ProfessionalLeadRecipientResultV1;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;

interface ProfessionalLeadRecipientReaderV1
{
    public function read(
        ProfessionalId $professionalId,
        LeadRecipientObservedAt $observedAt,
    ): ProfessionalLeadRecipientResultV1;
}
