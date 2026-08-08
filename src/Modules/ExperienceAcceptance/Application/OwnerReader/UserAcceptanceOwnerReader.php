<?php

namespace Appart\Modules\ExperienceAcceptance\Application\OwnerReader;

use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceOwnerSource;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceReadStatus;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceScopeKey;
use Appart\Modules\ExperienceAcceptance\Application\OwnerSource\ExperienceAcceptanceStream;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\UserAcceptanceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\UserAcceptanceResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\UserAcceptanceStatusV1;

final readonly class UserAcceptanceOwnerReader implements UserAcceptanceReaderV1
{
    private const SCOPE = 'experience:primary';

    public function __construct(private ExperienceAcceptanceOwnerSource $source) {}

    public function read(ExperienceAcceptanceObservedAt $observedAt): UserAcceptanceResultV1
    {
        $result = $this->source->read(new ExperienceAcceptanceScopeKey(self::SCOPE), ExperienceAcceptanceStream::UserAcceptance, $observedAt);
        $status = match ($result->status) {
            ExperienceAcceptanceReadStatus::Found => UserAcceptanceStatusV1::from($result->revision->decision),
            ExperienceAcceptanceReadStatus::Missing => UserAcceptanceStatusV1::Missing,
            ExperienceAcceptanceReadStatus::Corrupted => UserAcceptanceStatusV1::Corrupted,
            ExperienceAcceptanceReadStatus::DependencyUnavailable => UserAcceptanceStatusV1::DependencyUnavailable,
        };

        return new UserAcceptanceResultV1($status, $observedAt);
    }
}
