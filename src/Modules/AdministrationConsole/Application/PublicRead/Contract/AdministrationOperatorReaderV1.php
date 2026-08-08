<?php

namespace Appart\Modules\AdministrationConsole\Application\PublicRead\Contract;

use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationObservedAt;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;

interface AdministrationOperatorReaderV1
{
    public function read(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationOperatorResultV1;
}
