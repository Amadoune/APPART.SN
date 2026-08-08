<?php

namespace Appart\Modules\AdministrationConsole\Application\OwnerReader;

use Appart\Modules\AdministrationConsole\Application\OwnerReader\Contract\AdministrationConsoleOwnerReaderV1;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationConsoleOwnerSource;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationObservedAt;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;
use Appart\Modules\AdministrationConsole\Application\PublicRead\Contract\AdministrationQueueReaderV1;
use LogicException;

final readonly class AdministrationQueueOwnerReader implements AdministrationQueueReaderV1
{
    public function __construct(private AdministrationConsoleOwnerSource $source, private AdministrationConsoleOwnerReaderV1 $policy) {}

    public function read(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationQueueResultV1
    {
        $status = match ($this->policy->queue($this->source->readQueue($subject, $observedAt))->status) {
            AdministrationConsoleOwnerReaderStatus::Ready => AdministrationQueueStatusV1::Ready,
            AdministrationConsoleOwnerReaderStatus::Empty => AdministrationQueueStatusV1::Empty,
            AdministrationConsoleOwnerReaderStatus::Missing => AdministrationQueueStatusV1::Missing,
            AdministrationConsoleOwnerReaderStatus::Corrupted => AdministrationQueueStatusV1::Corrupted,
            AdministrationConsoleOwnerReaderStatus::DependencyUnavailable => AdministrationQueueStatusV1::DependencyUnavailable,
            AdministrationConsoleOwnerReaderStatus::Available,
            AdministrationConsoleOwnerReaderStatus::Unavailable => throw new LogicException('Invalid queue owner status.'),
        };

        return new AdministrationQueueResultV1($status);
    }
}
