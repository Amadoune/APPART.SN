<?php

namespace Appart\Modules\AdministrationConsole\Application\OwnerReader;

use Appart\Modules\AdministrationConsole\Application\OwnerReader\Contract\AdministrationConsoleOwnerReaderV1;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationAuditReadResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationOperatorReadResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationQueueReadResult;

final readonly class AdministrationConsoleOwnerReaderPolicy implements AdministrationConsoleOwnerReaderV1
{
    public function operator(AdministrationOperatorReadResult $result): AdministrationConsoleOwnerReaderResult
    {
        return new AdministrationConsoleOwnerReaderResult(AdministrationConsoleOwnerReaderStatus::from($result->status->value));
    }

    public function queue(AdministrationQueueReadResult $result): AdministrationConsoleOwnerReaderResult
    {
        return new AdministrationConsoleOwnerReaderResult(AdministrationConsoleOwnerReaderStatus::from($result->status->value));
    }

    public function audit(AdministrationAuditReadResult $result): AdministrationConsoleOwnerReaderResult
    {
        return new AdministrationConsoleOwnerReaderResult(AdministrationConsoleOwnerReaderStatus::from($result->status->value));
    }
}
