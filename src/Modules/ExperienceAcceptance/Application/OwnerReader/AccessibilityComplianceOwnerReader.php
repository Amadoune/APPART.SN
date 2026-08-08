<?php

namespace Appart\Modules\ExperienceAcceptance\Application\OwnerReader;

use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceOwnerSource;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceReadStatus;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceScopeKey;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceStream;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\AccessibilityComplianceResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\AccessibilityComplianceStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\AccessibilityComplianceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;

final readonly class AccessibilityComplianceOwnerReader implements AccessibilityComplianceReaderV1
{
    private const SCOPE = 'experience:primary';

    public function __construct(private ExperienceAcceptanceOwnerSource $source) {}

    public function read(ExperienceAcceptanceObservedAt $observedAt): AccessibilityComplianceResultV1
    {
        $result = $this->source->read(new ExperienceAcceptanceScopeKey(self::SCOPE), ExperienceAcceptanceStream::AccessibilityCompliance, $observedAt);
        $status = match ($result->status) {
            ExperienceAcceptanceReadStatus::Found => AccessibilityComplianceStatusV1::from($result->revision->decision),
            ExperienceAcceptanceReadStatus::Missing => AccessibilityComplianceStatusV1::Missing,
            ExperienceAcceptanceReadStatus::Corrupted => AccessibilityComplianceStatusV1::Corrupted,
            ExperienceAcceptanceReadStatus::DependencyUnavailable => AccessibilityComplianceStatusV1::DependencyUnavailable,
        };

        return new AccessibilityComplianceResultV1($status, $observedAt);
    }
}
