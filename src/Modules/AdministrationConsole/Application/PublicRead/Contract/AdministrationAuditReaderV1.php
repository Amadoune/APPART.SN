<?php

namespace Appart\Modules\AdministrationConsole\Application\PublicRead\Contract;

use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationObservedAt;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;

interface AdministrationAuditReaderV1
{
    public function read(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationAuditResultV1;
}
