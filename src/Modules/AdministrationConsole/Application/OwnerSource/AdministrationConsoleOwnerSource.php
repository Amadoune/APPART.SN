<?php

namespace Appart\Modules\AdministrationConsole\Application\OwnerSource;

use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationObservedAt;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;

interface AdministrationConsoleOwnerSource
{
    public function appendOperator(AdministrationOperatorRevisionState $revision): AdministrationOperatorWriteResult;

    public function appendQueue(AdministrationQueueRevisionState $revision): AdministrationQueueWriteResult;

    public function appendAudit(AdministrationAuditRevisionState $revision): AdministrationAuditWriteResult;

    public function readOperator(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationOperatorReadResult;

    public function readQueue(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationQueueReadResult;

    public function readAudit(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationAuditReadResult;
}
