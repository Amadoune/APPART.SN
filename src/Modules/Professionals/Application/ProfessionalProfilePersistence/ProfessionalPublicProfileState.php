<?php

namespace Appart\Modules\Professionals\Application\ProfessionalProfilePersistence;

use DateTimeImmutable;

final readonly class ProfessionalPublicProfileState
{
    /**
     * @param  list<string>  $categories
     * @param  list<string>  $languages
     * @param  array<string, string>  $publicContacts
     * @param  list<string>  $mediaReferences
     */
    public function __construct(
        public string $professionalId,
        public PublicProfileVisibility $visibility,
        public string $publicName,
        public string $description,
        public array $categories,
        public array $languages,
        public array $publicContacts,
        public array $mediaReferences,
        public int $revision,
        public int $version,
        public string $policyVersion,
        public string $intentId,
        public string $intentChecksum,
        public DateTimeImmutable $updatedAt,
    ) {}
}
