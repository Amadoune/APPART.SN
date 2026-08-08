<?php

namespace Appart\Modules\ExperienceAcceptance\Application\OwnerReader;

use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceOwnerSource;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceReadStatus;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceScopeKey;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceStream;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\PerformanceReadinessReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\PerformanceReadinessResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\PerformanceReadinessStatusV1;

final readonly class PerformanceReadinessOwnerReader implements PerformanceReadinessReaderV1
{
    private const SCOPE = 'experience:primary';

    public function __construct(private ExperienceAcceptanceOwnerSource $source) {}

    public function read(ExperienceAcceptanceObservedAt $observedAt): PerformanceReadinessResultV1
    {
        $result = $this->source->read(new ExperienceAcceptanceScopeKey(self::SCOPE), ExperienceAcceptanceStream::PerformanceReadiness, $observedAt);
        $status = match ($result->status) {
            ExperienceAcceptanceReadStatus::Found => PerformanceReadinessStatusV1::from($result->revision->decision),
            ExperienceAcceptanceReadStatus::Missing => PerformanceReadinessStatusV1::Missing,
            ExperienceAcceptanceReadStatus::Corrupted => PerformanceReadinessStatusV1::Corrupted,
            ExperienceAcceptanceReadStatus::DependencyUnavailable => PerformanceReadinessStatusV1::DependencyUnavailable,
        };

        return new PerformanceReadinessResultV1($status, $observedAt);
    }
}
