<?php

namespace Appart\Modules\ContentSeo\Infrastructure\Persistence;

use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSourceDecision;
use Appart\Modules\ContentSeo\Domain\Model\CanonicalHistoryEntry;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PropertySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SearchSeoSource;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalDisposition;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\ExpiredListingTreatment;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\PropertySeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SearchSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoPageTreatment;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceKind;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceRevision;
use DateTimeImmutable;
use JsonException;
use RuntimeException;
use Throwable;

final readonly class ContentSeoSourceSnapshotMapper
{
    /** @return array{snapshot_id:string,listing_id:string,version:int,payload:string,checksum:string} */
    public function parameters(ContentSeoSourceDecision $snapshot): array
    {
        try {
            $payload = json_encode($this->payload($snapshot), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $error) {
            throw new RuntimeException('Unable to encode Content/SEO source snapshot.', 0, $error);
        }

        return ['snapshot_id' => $snapshot->snapshotId, 'listing_id' => $snapshot->listingId->value, 'version' => $snapshot->version, 'payload' => $payload, 'checksum' => hash('sha256', $payload)];
    }

    /** @param array<string, mixed> $row */
    public function toSnapshot(array $row): ContentSeoSourceDecision
    {
        try {
            $payload = json_decode((string) $row['payload'], true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($payload)) {
                throw new RuntimeException('Invalid Content/SEO source snapshot payload.');
            }
            $listingId = ListingId::fromString((string) $row['listing_id']);
            $listing = (array) ($payload['listing'] ?? []);
            $search = (array) ($payload['search'] ?? []);
            $property = (array) ($payload['property'] ?? []);
            $history = [];
            foreach ((array) ($payload['canonical_history'] ?? []) as $entry) {
                if (! is_array($entry)) {
                    throw new RuntimeException('Invalid canonical history entry.');
                }
                $history[] = new CanonicalHistoryEntry(
                    CanonicalUrl::fromString((string) ($entry['canonical'] ?? '')),
                    CanonicalDisposition::from((string) ($entry['disposition'] ?? '')),
                    new DateTimeImmutable((string) ($entry['effective_at'] ?? '')),
                    $this->date($entry['replaced_at'] ?? null),
                    ($entry['redirect_target'] ?? null) === null ? null : CanonicalUrl::fromString((string) $entry['redirect_target']),
                );
            }
            $snapshot = new ContentSeoSourceDecision(
                (string) $row['snapshot_id'],
                $listingId,
                (int) $row['version'],
                new ListingSeoSource($listingId, ListingSeoState::from((string) ($listing['state'] ?? '')), (string) ($listing['headline'] ?? ''), (string) ($listing['description'] ?? ''), (string) ($listing['canonical_path'] ?? ''), $this->revision((array) ($listing['revision'] ?? [])), $this->date($listing['published_at'] ?? null), $this->date($listing['expires_at'] ?? null), ExpiredListingTreatment::from((string) ($listing['expired_treatment'] ?? '')), SeoPageTreatment::from((string) ($listing['non_indexable_treatment'] ?? ''))),
                new SearchSeoSource($listingId, SearchSeoState::from((string) ($search['state'] ?? '')), $this->revision((array) ($search['revision'] ?? []))),
                new PropertySeoSource($listingId, PropertySeoState::from((string) ($property['state'] ?? '')), (string) ($property['property_type'] ?? ''), (string) ($property['city'] ?? ''), $this->revision((array) ($property['revision'] ?? []))),
                $history,
                new DateTimeImmutable((string) ($payload['decision_at'] ?? '')),
            );
            $parameters = $this->parameters($snapshot);
            if (! hash_equals((string) $row['payload_checksum'], $parameters['checksum'])) {
                throw new RuntimeException('Corrupt Content/SEO source snapshot checksum.');
            }

            return $snapshot;
        } catch (Throwable $error) {
            throw new RuntimeException('Corrupt or invalid Content/SEO source snapshot.', 0, $error);
        }
    }

    /** @return array<string, mixed> */
    private function payload(ContentSeoSourceDecision $snapshot): array
    {
        return [
            'listing' => ['state' => $snapshot->listing->state->value, 'headline' => $snapshot->listing->headline, 'description' => $snapshot->listing->description, 'canonical_path' => $snapshot->listing->canonicalPath, 'revision' => $this->revisionPayload($snapshot->listing->revision), 'published_at' => $this->format($snapshot->listing->publishedAt), 'expires_at' => $this->format($snapshot->listing->expiresAt), 'expired_treatment' => $snapshot->listing->expiredTreatment->value, 'non_indexable_treatment' => $snapshot->listing->nonIndexablePageTreatment->value],
            'search' => ['state' => $snapshot->search->state->value, 'revision' => $this->revisionPayload($snapshot->search->revision)],
            'property' => ['state' => $snapshot->property->state->value, 'property_type' => $snapshot->property->propertyType, 'city' => $snapshot->property->city, 'revision' => $this->revisionPayload($snapshot->property->revision)],
            'canonical_history' => array_map(fn (CanonicalHistoryEntry $entry): array => ['canonical' => $entry->canonical->value, 'disposition' => $entry->disposition->value, 'effective_at' => $this->format($entry->effectiveAt), 'replaced_at' => $this->format($entry->replacedAt), 'redirect_target' => $entry->redirectTarget?->value], $snapshot->canonicalHistory),
            'decision_at' => $this->format($snapshot->decisionAt),
        ];
    }

    /** @return array{source:string,version:int,fact_id:string,coherence_id:string,effective_at:string|null} */
    private function revisionPayload(SeoSourceRevision $revision): array
    {
        return ['source' => $revision->source->value, 'version' => $revision->version, 'fact_id' => $revision->factId, 'coherence_id' => $revision->coherenceId, 'effective_at' => $this->format($revision->effectiveAt)];
    }

    /** @param array<string, mixed> $payload */
    private function revision(array $payload): SeoSourceRevision
    {
        return SeoSourceRevision::create(SeoSourceKind::from((string) ($payload['source'] ?? '')), (int) ($payload['version'] ?? 0), (string) ($payload['fact_id'] ?? ''), (string) ($payload['coherence_id'] ?? ''), new DateTimeImmutable((string) ($payload['effective_at'] ?? '')));
    }

    private function format(?DateTimeImmutable $date): ?string
    {
        return $date?->format('Y-m-d\TH:i:s.uP');
    }

    private function date(mixed $value): ?DateTimeImmutable
    {
        return $value === null ? null : new DateTimeImmutable((string) $value);
    }
}
