<?php

namespace Appart\Modules\SearchDiscovery\Infrastructure\Persistence;

use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerRevisionDecision;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerRevisionState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

final readonly class SearchOwnerSourceMapper
{
    public function __construct(private SearchDecisionMapper $decisionMapper = new SearchDecisionMapper) {}

    /** @return array{document_id:string,revision:int,decision_id:string,listing_id:string,state:string,payload:string,payload_checksum:string,effective_at:string,recorded_at:string,revision_checksum:string} */
    public function toRow(SearchOwnerRevisionState $revision): array
    {
        $decision = $this->decisionMapper->parameters($revision->searchDecision);
        $row = [
            'document_id' => $revision->documentId->value,
            'revision' => $revision->revision,
            'decision_id' => $decision['decision_id'],
            'listing_id' => $decision['listing_id'],
            'state' => $revision->decision->value,
            'payload' => $decision['payload'],
            'payload_checksum' => $decision['checksum'],
            'effective_at' => $this->canonical($revision->effectiveAt),
            'recorded_at' => $this->canonical($revision->recordedAt),
        ];

        return $row + ['revision_checksum' => $this->checksum($row)];
    }

    /** @param array<string, mixed> $row */
    public function toState(array $row): SearchOwnerRevisionState
    {
        try {
            if (! hash_equals((string) $row['revision_checksum'], $this->checksum($row))) {
                throw new RuntimeException('Search owner revision checksum mismatch.');
            }
            $decision = $this->decisionMapper->toDecision([
                'decision_id' => $row['decision_id'],
                'listing_id' => $row['listing_id'],
                'version' => $row['revision'],
                'state' => $row['state'],
                'payload' => $row['payload'],
                'payload_checksum' => $row['payload_checksum'],
            ]);

            return new SearchOwnerRevisionState(
                SearchDocumentId::fromString((string) $row['document_id']),
                (int) $row['revision'],
                SearchOwnerRevisionDecision::from((string) $row['state']),
                $decision,
                new DateTimeImmutable((string) $row['effective_at']),
                new DateTimeImmutable((string) $row['recorded_at']),
            );
        } catch (Throwable $error) {
            throw new RuntimeException('Invalid Search owner source row.', 0, $error);
        }
    }

    /** @param array<string, mixed> $row */
    public function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [
            strtolower((string) $row['document_id']),
            (string) $row['revision'],
            strtolower((string) $row['decision_id']),
            strtolower((string) $row['listing_id']),
            (string) $row['state'],
            (string) $row['payload_checksum'],
            $this->canonical(new DateTimeImmutable((string) $row['effective_at'])),
            $this->canonical(new DateTimeImmutable((string) $row['recorded_at'])),
        ]));
    }

    private function canonical(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
