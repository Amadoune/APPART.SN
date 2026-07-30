<?php

namespace Appart\Modules\Professionals\Infrastructure\Persistence;

use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalPublicPortfolioState;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalPublicProfileState;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalVerificationState;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\PublicProfileVisibility;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\VerificationDisposition;
use DateTimeImmutable;

final class ProfessionalProfilePersistenceMapper
{
    /** @return array<string, mixed> */
    public function profileParameters(ProfessionalPublicProfileState $state): array
    {
        return [
            'professional_id' => $state->professionalId,
            'visibility' => $state->visibility->value,
            'public_name' => $state->publicName,
            'description' => $state->description,
            'categories' => $this->json($state->categories),
            'languages' => $this->json($state->languages),
            'public_contacts' => $this->json((object) $state->publicContacts),
            'media_references' => $this->json($state->mediaReferences),
            'revision' => $state->revision,
            'version' => $state->version,
            'policy_version' => $state->policyVersion,
            'intent_id' => $state->intentId,
            'intent_checksum' => $state->intentChecksum,
            'updated_at' => $this->date($state->updatedAt),
        ];
    }

    /** @param array<string, mixed> $row */
    public function profileState(array $row): ProfessionalPublicProfileState
    {
        return new ProfessionalPublicProfileState(
            (string) $row['professional_id'],
            PublicProfileVisibility::from((string) $row['visibility']),
            (string) $row['public_name'],
            (string) $row['description'],
            $this->stringList((string) $row['categories']),
            $this->stringList((string) $row['languages']),
            $this->stringMap((string) $row['public_contacts']),
            $this->stringList((string) $row['media_references']),
            (int) $row['revision'],
            (int) $row['version'],
            (string) $row['policy_version'],
            (string) $row['last_intent_id'],
            (string) $row['last_intent_checksum'],
            new DateTimeImmutable((string) $row['updated_at']),
        );
    }

    /** @return array<string, mixed> */
    public function verificationParameters(ProfessionalVerificationState $state): array
    {
        return [
            'professional_id' => $state->professionalId,
            'disposition' => $state->disposition->value,
            'evidence_references' => $this->json($state->evidenceReferences),
            'policy_version' => $state->policyVersion,
            'decision_authority_id' => $state->decisionAuthorityId,
            'expires_at' => $state->expiresAt?->format('Y-m-d\TH:i:s.uP'),
            'decision_sequence' => $state->decisionSequence,
            'version' => $state->version,
            'intent_id' => $state->intentId,
            'intent_checksum' => $state->intentChecksum,
            'updated_at' => $this->date($state->updatedAt),
        ];
    }

    /** @param array<string, mixed> $row */
    public function verificationState(array $row): ProfessionalVerificationState
    {
        return new ProfessionalVerificationState(
            (string) $row['professional_id'],
            VerificationDisposition::from((string) $row['disposition']),
            $this->stringList((string) $row['evidence_references']),
            (string) $row['policy_version'],
            $row['decision_authority_id'] === null ? null : (string) $row['decision_authority_id'],
            $row['expires_at'] === null ? null : new DateTimeImmutable((string) $row['expires_at']),
            (int) $row['decision_sequence'],
            (int) $row['version'],
            (string) $row['last_intent_id'],
            (string) $row['last_intent_checksum'],
            new DateTimeImmutable((string) $row['updated_at']),
        );
    }

    /** @return array<string, mixed> */
    public function portfolioParameters(ProfessionalPublicPortfolioState $state): array
    {
        return [
            'professional_id' => $state->professionalId,
            'listing_ids' => $this->json($state->listingIds),
            'checkpoint' => $state->checkpoint,
            'version' => $state->version,
            'intent_id' => $state->intentId,
            'intent_checksum' => $state->intentChecksum,
            'updated_at' => $this->date($state->updatedAt),
        ];
    }

    /** @param array<string, mixed> $row */
    public function portfolioState(array $row): ProfessionalPublicPortfolioState
    {
        return new ProfessionalPublicPortfolioState(
            (string) $row['professional_id'],
            $this->stringList((string) $row['listing_ids']),
            (int) $row['checkpoint'],
            (int) $row['version'],
            (string) $row['last_intent_id'],
            (string) $row['last_intent_checksum'],
            new DateTimeImmutable((string) $row['updated_at']),
        );
    }

    public function profileSnapshot(ProfessionalPublicProfileState $state): string
    {
        return $this->json($this->profileParameters($state));
    }

    private function date(DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d\TH:i:s.uP');
    }

    private function json(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /** @return list<string> */
    private function stringList(string $json): array
    {
        $value = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($value) || ! array_is_list($value)) {
            throw new \UnexpectedValueException('Expected a JSON string list.');
        }

        return array_map(static fn (mixed $item): string => is_string($item) ? $item : throw new \UnexpectedValueException('Expected string list item.'), $value);
    }

    /** @return array<string, string> */
    private function stringMap(string $json): array
    {
        $value = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($value)) {
            throw new \UnexpectedValueException('Expected a JSON string map.');
        }
        foreach ($value as $key => $item) {
            if (! is_string($key) || ! is_string($item)) {
                throw new \UnexpectedValueException('Expected string map entry.');
            }
        }

        return $value;
    }
}
