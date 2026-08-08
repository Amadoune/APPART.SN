<?php

namespace Appart\Modules\AdministrationConsole\Application\OwnerReader;

use Appart\Modules\AdministrationConsole\Application\OwnerReader\Contract\AdministrationConsoleOwnerReaderV1;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationConsoleOwnerSource;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationObservedAt;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;
use Appart\Modules\AdministrationConsole\Application\PublicRead\Contract\AdministrationOperatorReaderV1;
use LogicException;

final readonly class AdministrationOperatorOwnerReader implements AdministrationOperatorReaderV1
{
    public function __construct(private AdministrationConsoleOwnerSource $source, private AdministrationConsoleOwnerReaderV1 $policy) {}

    public function read(AdministrationSubjectKey $subject, AdministrationObservedAt $observedAt): AdministrationOperatorResultV1
    {
        $status = match ($this->policy->operator($this->source->readOperator($subject, $observedAt))->status) {
            AdministrationConsoleOwnerReaderStatus::Available => AdministrationOperatorStatusV1::Available,
            AdministrationConsoleOwnerReaderStatus::Unavailable => AdministrationOperatorStatusV1::Unavailable,
            AdministrationConsoleOwnerReaderStatus::Missing => AdministrationOperatorStatusV1::Missing,
            AdministrationConsoleOwnerReaderStatus::Corrupted => AdministrationOperatorStatusV1::Corrupted,
            AdministrationConsoleOwnerReaderStatus::DependencyUnavailable => AdministrationOperatorStatusV1::DependencyUnavailable,
            AdministrationConsoleOwnerReaderStatus::Ready,
            AdministrationConsoleOwnerReaderStatus::Empty => throw new LogicException('Invalid operator owner status.'),
        };

        return new AdministrationOperatorResultV1($status);
    }
}
