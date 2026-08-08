<?php

namespace Appart\Modules\AdministrationConsole\Application\Event;

use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationObservedAt;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;
use Appart\Modules\AdministrationConsole\Application\PublicRead\Contract\AdministrationQueueReaderV1;

final readonly class AdministrationQueueEventFactory
{
    public function __construct(private AdministrationQueueReaderV1 $reader) {}

    public function create(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationQueueEventV1
    {
        $status = match ($this->reader->read($subject, $observedAt)->status) {
            AdministrationQueueStatusV1::Ready => AdministrationQueueEventStatus::Ready,
            AdministrationQueueStatusV1::Empty => AdministrationQueueEventStatus::Empty,
            AdministrationQueueStatusV1::Missing => AdministrationQueueEventStatus::Missing,
            AdministrationQueueStatusV1::Corrupted => AdministrationQueueEventStatus::Corrupted,
            AdministrationQueueStatusV1::DependencyUnavailable => AdministrationQueueEventStatus::DependencyUnavailable,
        };

        return new AdministrationQueueEventV1(AdministrationQueueEventType::Observed, new AdministrationQueueEventPayload($status, $observedAt->canonical()));
    }
}
