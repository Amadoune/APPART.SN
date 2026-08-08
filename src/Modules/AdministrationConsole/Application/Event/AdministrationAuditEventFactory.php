<?php

namespace Appart\Modules\AdministrationConsole\Application\Event;

use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationObservedAt;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;
use Appart\Modules\AdministrationConsole\Application\PublicRead\Contract\AdministrationAuditReaderV1;

final readonly class AdministrationAuditEventFactory
{
    public function __construct(private AdministrationAuditReaderV1 $reader) {}

    public function create(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationAuditEventV1
    {
        $status = match ($this->reader->read($subject, $observedAt)->status) {
            AdministrationAuditStatusV1::Available => AdministrationAuditEventStatus::Available,
            AdministrationAuditStatusV1::Missing => AdministrationAuditEventStatus::Missing,
            AdministrationAuditStatusV1::Corrupted => AdministrationAuditEventStatus::Corrupted,
            AdministrationAuditStatusV1::DependencyUnavailable => AdministrationAuditEventStatus::DependencyUnavailable,
        };

        return new AdministrationAuditEventV1(AdministrationAuditEventType::Observed, new AdministrationAuditEventPayload($status, $observedAt->canonical()));
    }
}
