<?php

namespace Appart\Modules\ExperienceAcceptance\Application\OwnerReader;

use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceOwnerSource;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceReadStatus;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceScopeKey;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceStream;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\ReleaseCandidateReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ReleaseCandidateResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ReleaseCandidateStatusV1;

final readonly class ReleaseCandidateOwnerReader implements ReleaseCandidateReaderV1
{
    private const SCOPE = 'experience:primary';

    public function __construct(private ExperienceAcceptanceOwnerSource $source) {}

    public function read(ExperienceAcceptanceObservedAt $observedAt): ReleaseCandidateResultV1
    {
        $result = $this->source->read(new ExperienceAcceptanceScopeKey(self::SCOPE), ExperienceAcceptanceStream::ReleaseCandidate, $observedAt);
        $status = match ($result->status) {
            ExperienceAcceptanceReadStatus::Found => ReleaseCandidateStatusV1::from($result->revision->decision),
            ExperienceAcceptanceReadStatus::Missing => ReleaseCandidateStatusV1::Missing,
            ExperienceAcceptanceReadStatus::Corrupted => ReleaseCandidateStatusV1::Corrupted,
            ExperienceAcceptanceReadStatus::DependencyUnavailable => ReleaseCandidateStatusV1::DependencyUnavailable,
        };

        return new ReleaseCandidateResultV1($status, $observedAt);
    }
}
