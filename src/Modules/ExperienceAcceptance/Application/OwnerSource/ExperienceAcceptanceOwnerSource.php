<?php

namespace Appart\Modules\ExperienceAcceptance\Application\OwnerSource;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;

interface ExperienceAcceptanceOwnerSource
{
    public function append(ExperienceAcceptanceRevisionState $revision): ExperienceAcceptanceWriteResult;

    public function read(ExperienceAcceptanceScopeKey $scope, ExperienceAcceptanceStream $stream, ExperienceAcceptanceObservedAt $observedAt): ExperienceAcceptanceReadResult;
}
