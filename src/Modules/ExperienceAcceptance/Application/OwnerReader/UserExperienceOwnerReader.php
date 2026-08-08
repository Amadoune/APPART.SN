<?php

namespace Appart\Modules\ExperienceAcceptance\Application\OwnerReader;

use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceOwnerSource;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceReadStatus;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceScopeKey;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceStream;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\UserExperienceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\UserExperienceResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\UserExperienceStatusV1;

final readonly class UserExperienceOwnerReader implements UserExperienceReaderV1
{
    private const SCOPE = 'experience:primary';

    public function __construct(private ExperienceAcceptanceOwnerSource $source) {}

    public function read(ExperienceAcceptanceObservedAt $observedAt): UserExperienceResultV1
    {
        $result = $this->source->read(new ExperienceAcceptanceScopeKey(self::SCOPE), ExperienceAcceptanceStream::UserExperience, $observedAt);
        $status = match ($result->status) {
            ExperienceAcceptanceReadStatus::Found => UserExperienceStatusV1::from($result->revision->decision),
            ExperienceAcceptanceReadStatus::Missing => UserExperienceStatusV1::Missing,
            ExperienceAcceptanceReadStatus::Corrupted => UserExperienceStatusV1::Corrupted,
            ExperienceAcceptanceReadStatus::DependencyUnavailable => UserExperienceStatusV1::DependencyUnavailable,
        };

        return new UserExperienceResultV1($status, $observedAt);
    }
}
