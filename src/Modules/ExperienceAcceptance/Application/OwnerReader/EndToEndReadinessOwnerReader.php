<?php

namespace Appart\Modules\ExperienceAcceptance\Application\OwnerReader;

use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceOwnerSource;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceReadStatus;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceScopeKey;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceStream;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\EndToEndReadinessReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\EndToEndReadinessResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\EndToEndReadinessStatusV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;

final readonly class EndToEndReadinessOwnerReader implements EndToEndReadinessReaderV1
{
    private const SCOPE = 'experience:primary';

    public function __construct(private ExperienceAcceptanceOwnerSource $source) {}

    public function read(ExperienceAcceptanceObservedAt $observedAt): EndToEndReadinessResultV1
    {
        $result = $this->source->read(new ExperienceAcceptanceScopeKey(self::SCOPE), ExperienceAcceptanceStream::EndToEndReadiness, $observedAt);
        $status = match ($result->status) {
            ExperienceAcceptanceReadStatus::Found => EndToEndReadinessStatusV1::from($result->revision->decision),
            ExperienceAcceptanceReadStatus::Missing => EndToEndReadinessStatusV1::Missing,
            ExperienceAcceptanceReadStatus::Corrupted => EndToEndReadinessStatusV1::Corrupted,
            ExperienceAcceptanceReadStatus::DependencyUnavailable => EndToEndReadinessStatusV1::DependencyUnavailable,
        };

        return new EndToEndReadinessResultV1($status, $observedAt);
    }
}
