<?php

namespace Appart\Modules\ExperienceAcceptance\Application\OwnerSource;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class ExperienceAcceptanceRevisionState
{
    public string $scopeKey;

    public DateTimeImmutable $effectiveAt;

    public DateTimeImmutable $recordedAt;

    public function __construct(ExperienceAcceptanceScopeKey|string $scope, public ExperienceAcceptanceStream $stream, public int $revision, public string $decision, DateTimeImmutable $effectiveAt, DateTimeImmutable $recordedAt)
    {
        if ($revision < 1 || ! in_array($decision, self::decisions($stream), true)) {
            throw new InvalidArgumentException('Experience Acceptance revision is invalid.');
        }
        $this->scopeKey = ($scope instanceof ExperienceAcceptanceScopeKey ? $scope : new ExperienceAcceptanceScopeKey($scope))->value;
        $utc = new DateTimeZone('UTC');
        $this->effectiveAt = $effectiveAt->setTimezone($utc);
        $this->recordedAt = $recordedAt->setTimezone($utc);
        if ($this->recordedAt < $this->effectiveAt) {
            throw new InvalidArgumentException('Experience Acceptance chronology is invalid.');
        }
    }

    /** @return non-empty-list<string> */
    private static function decisions(ExperienceAcceptanceStream $stream): array
    {
        return match ($stream) {
            ExperienceAcceptanceStream::ResponsiveCompliance,
            ExperienceAcceptanceStream::AccessibilityCompliance,
            ExperienceAcceptanceStream::UserExperience,
            ExperienceAcceptanceStream::EndToEndReadiness,
            ExperienceAcceptanceStream::PerformanceReadiness,
            ExperienceAcceptanceStream::UserAcceptance,
            ExperienceAcceptanceStream::ReleaseCandidate => ['available', 'missing', 'corrupted', 'dependency_unavailable'],
        };
    }
}
