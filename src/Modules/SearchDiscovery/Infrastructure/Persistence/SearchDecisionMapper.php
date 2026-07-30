<?php

namespace Appart\Modules\SearchDiscovery\Infrastructure\Persistence;

use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecision;
use Appart\Modules\SearchDiscovery\Domain\Model\SearchFacet;
use Appart\Modules\SearchDiscovery\Domain\Model\SearchProjection;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ProjectionState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchFacetKey;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchFacetValue;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchRank;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceKind;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevision;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevisionSet;
use DateTimeImmutable;
use JsonException;
use RuntimeException;
use Throwable;

final readonly class SearchDecisionMapper
{
    /** @return array{decision_id:string,listing_id:string,version:int,state:string,payload:string,checksum:string} */
    public function parameters(SearchDecision $decision): array
    {
        try {
            $payload = json_encode($this->payload($decision), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $error) {
            throw new RuntimeException('Unable to encode final Search decision.', 0, $error);
        }

        return [
            'decision_id' => $decision->decisionId->value,
            'listing_id' => $decision->listingId->value,
            'version' => $decision->version,
            'state' => $decision->projection->state->value,
            'payload' => $payload,
            'checksum' => hash('sha256', $payload),
        ];
    }

    /** @param array<string, mixed> $row */
    public function toDecision(array $row): SearchDecision
    {
        try {
            $payload = json_decode((string) $row['payload'], true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($payload)) {
                throw new RuntimeException('Invalid final Search decision payload.');
            }
            $revisions = [];
            foreach ((array) ($payload['revisions'] ?? []) as $revision) {
                if (! is_array($revision)) {
                    throw new RuntimeException('Invalid Search source revision.');
                }
                $source = SourceKind::from((string) ($revision['source'] ?? ''));
                $revisions[$source->value] = SourceRevision::create($source, (int) ($revision['version'] ?? 0), (string) ($revision['fact_id'] ?? ''), new DateTimeImmutable((string) ($revision['effective_at'] ?? '')));
            }
            $facets = [];
            foreach ((array) ($payload['facets'] ?? []) as $facet) {
                if (! is_array($facet)) {
                    throw new RuntimeException('Invalid Search facet.');
                }
                $facets[] = new SearchFacet(SearchFacetKey::fromString((string) ($facet['key'] ?? '')), SearchFacetValue::fromString((string) ($facet['value'] ?? '')), SourceKind::from((string) ($facet['source'] ?? '')));
            }
            $projection = SearchProjection::derived(
                ProjectionState::from((string) ($payload['state'] ?? '')),
                SearchRank::fromInt((int) ($payload['rank'] ?? -1)),
                $facets,
                new SourceRevisionSet($revisions['listing'], $revisions['property'], $revisions['media']),
            );
            $decision = new SearchDecision(SearchIndexId::fromString((string) $row['decision_id']), ListingId::fromString((string) $row['listing_id']), (int) $row['version'], $projection);
            $parameters = $this->parameters($decision);
            if ($parameters['state'] !== (string) $row['state'] || ! hash_equals((string) $row['payload_checksum'], $parameters['checksum'])) {
                throw new RuntimeException('Corrupt final Search decision checksum.');
            }

            return $decision;
        } catch (Throwable $error) {
            throw new RuntimeException('Corrupt or invalid final Search decision.', 0, $error);
        }
    }

    /** @return array{state:string,rank:int,facets:list<array{key:string,value:string,source:string}>,revisions:list<array{source:string,version:int,fact_id:string,effective_at:string}>} */
    private function payload(SearchDecision $decision): array
    {
        return [
            'state' => $decision->projection->state->value,
            'rank' => $decision->projection->rank->value,
            'facets' => array_map(static fn (SearchFacet $facet): array => ['key' => $facet->key->value, 'value' => $facet->value->value, 'source' => $facet->source->value], $decision->projection->facets),
            'revisions' => array_map(static fn (SourceRevision $revision): array => ['source' => $revision->source->value, 'version' => $revision->version, 'fact_id' => $revision->factId, 'effective_at' => $revision->effectiveAt->format('Y-m-d\TH:i:s.uP')], $decision->projection->revisions->all()),
        ];
    }
}
