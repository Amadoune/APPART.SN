<?php

namespace Appart\Modules\AdministrationConsole\Application\Event;

use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationObservedAt;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;
use Appart\Modules\AdministrationConsole\Application\PublicRead\Contract\AdministrationOperatorReaderV1;

final readonly class AdministrationOperatorEventFactory
{
    public function __construct(private AdministrationOperatorReaderV1 $reader) {}

    public function create(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationOperatorEventV1
    {
        $status = match ($this->reader->read($subject, $observedAt)->status) {
            AdministrationOperatorStatusV1::Available => AdministrationOperatorEventStatus::Available,
            AdministrationOperatorStatusV1::Unavailable => AdministrationOperatorEventStatus::Unavailable,
            AdministrationOperatorStatusV1::Missing => AdministrationOperatorEventStatus::Missing,
            AdministrationOperatorStatusV1::Corrupted => AdministrationOperatorEventStatus::Corrupted,
            AdministrationOperatorStatusV1::DependencyUnavailable => AdministrationOperatorEventStatus::DependencyUnavailable,
        };

        return new AdministrationOperatorEventV1(AdministrationOperatorEventType::Observed, new AdministrationOperatorEventPayload($status, $observedAt->canonical()));
    }
}
