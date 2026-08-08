<?php

namespace Appart\Modules\AdministrationConsole\Application\PublicRead\Contract;

use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationObservedAt;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;

interface AdministrationQueueReaderV1
{
    public function read(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationQueueResultV1;
}
