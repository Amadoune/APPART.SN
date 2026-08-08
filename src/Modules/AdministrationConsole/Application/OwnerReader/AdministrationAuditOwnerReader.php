<?php

namespace Appart\Modules\AdministrationConsole\Application\OwnerReader;

use Appart\Modules\AdministrationConsole\Application\OwnerReader\Contract\AdministrationConsoleOwnerReaderV1;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationConsoleOwnerSource;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationObservedAt;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;
use Appart\Modules\AdministrationConsole\Application\PublicRead\Contract\AdministrationAuditReaderV1;
use LogicException;

final readonly class AdministrationAuditOwnerReader implements AdministrationAuditReaderV1
{
    public function __construct(private AdministrationConsoleOwnerSource $source, private AdministrationConsoleOwnerReaderV1 $policy) {}

    public function read(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationAuditResultV1
    {
        $status = match ($this->policy->audit($this->source->readAudit($subject, $observedAt))->status) {
            AdministrationConsoleOwnerReaderStatus::Available => AdministrationAuditStatusV1::Available,
            AdministrationConsoleOwnerReaderStatus::Missing => AdministrationAuditStatusV1::Missing,
            AdministrationConsoleOwnerReaderStatus::Corrupted => AdministrationAuditStatusV1::Corrupted,
            AdministrationConsoleOwnerReaderStatus::DependencyUnavailable => AdministrationAuditStatusV1::DependencyUnavailable,
            AdministrationConsoleOwnerReaderStatus::Unavailable,
            AdministrationConsoleOwnerReaderStatus::Ready,
            AdministrationConsoleOwnerReaderStatus::Empty => throw new LogicException('Invalid audit owner status.'),
        };

        return new AdministrationAuditResultV1($status);
    }
}
