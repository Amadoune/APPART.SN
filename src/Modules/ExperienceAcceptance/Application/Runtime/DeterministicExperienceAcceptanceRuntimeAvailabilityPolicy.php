<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Runtime;

use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceOwnerSource;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceReadStatus;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceScopeKey;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceStream;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use DateTimeImmutable;
use Throwable;

final readonly class DeterministicExperienceAcceptanceRuntimeAvailabilityPolicy implements ExperienceAcceptanceRuntimeAvailabilityPolicy
{
    private const PROBE_SCOPE = 'runtime/experience-acceptance-owner-source';

    public function __construct(private ExperienceAcceptanceOwnerSource $source) {}

    public function inspect(): ExperienceAcceptanceRuntimeAvailability
    {
        try {
            $scope = new ExperienceAcceptanceScopeKey(self::PROBE_SCOPE);
            $observedAt = new ExperienceAcceptanceObservedAt(new DateTimeImmutable('9999-12-31T23:59:59.999999Z'));
            $corrupted = false;
            foreach (ExperienceAcceptanceStream::cases() as $stream) {
                $status = $this->source->read($scope, $stream, $observedAt)->status;
                if ($status === ExperienceAcceptanceReadStatus::DependencyUnavailable) {
                    return ExperienceAcceptanceRuntimeAvailability::DependencyUnavailable;
                }
                $corrupted = $corrupted || $status === ExperienceAcceptanceReadStatus::Corrupted;
            }

            return $corrupted ? ExperienceAcceptanceRuntimeAvailability::Corrupted : ExperienceAcceptanceRuntimeAvailability::Available;
        } catch (Throwable) {
            return ExperienceAcceptanceRuntimeAvailability::DependencyUnavailable;
        }
    }
}
