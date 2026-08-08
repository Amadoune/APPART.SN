<?php

namespace Appart\Modules\ExperienceAcceptance\Application\OwnerReader;

use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceOwnerSource;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceReadStatus;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceScopeKey;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceStream;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\ResponsiveComplianceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ResponsiveComplianceResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ResponsiveComplianceStatusV1;

final readonly class ResponsiveComplianceOwnerReader implements ResponsiveComplianceReaderV1
{
    private const SCOPE = 'experience:primary';

    public function __construct(private ExperienceAcceptanceOwnerSource $source) {}

    public function read(ExperienceAcceptanceObservedAt $observedAt): ResponsiveComplianceResultV1
    {
        $result = $this->source->read(new ExperienceAcceptanceScopeKey(self::SCOPE), ExperienceAcceptanceStream::ResponsiveCompliance, $observedAt);
        $status = match ($result->status) {
            ExperienceAcceptanceReadStatus::Found => ResponsiveComplianceStatusV1::from($result->revision->decision),
            ExperienceAcceptanceReadStatus::Missing => ResponsiveComplianceStatusV1::Missing,
            ExperienceAcceptanceReadStatus::Corrupted => ResponsiveComplianceStatusV1::Corrupted,
            ExperienceAcceptanceReadStatus::DependencyUnavailable => ResponsiveComplianceStatusV1::DependencyUnavailable,
        };

        return new ResponsiveComplianceResultV1($status, $observedAt);
    }
}
