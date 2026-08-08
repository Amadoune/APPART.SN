<?php

namespace Appart\Modules\AdministrationConsole\Application\OwnerReader\Contract;

use Appart\Modules\AdministrationConsole\Application\OwnerReader\AdministrationConsoleOwnerReaderResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationAuditReadResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationOperatorReadResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationQueueReadResult;

interface AdministrationConsoleOwnerReaderV1
{
    public function operator(AdministrationOperatorReadResult $result): AdministrationConsoleOwnerReaderResult;

    public function queue(AdministrationQueueReadResult $result): AdministrationConsoleOwnerReaderResult;

    public function audit(AdministrationAuditReadResult $result): AdministrationConsoleOwnerReaderResult;
}
