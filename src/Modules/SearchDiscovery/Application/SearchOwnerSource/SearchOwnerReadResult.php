<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSource;

use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;

final readonly class SearchOwnerReadResult
{
    private function __construct(
        public SearchDocumentId $documentId,
        public SearchOwnerReadStatus $status,
        public ?SearchOwnerRevisionState $revision,
    ) {}

    public static function found(SearchOwnerRevisionState $revision): self
    {
        return new self($revision->documentId, SearchOwnerReadStatus::Found, $revision);
    }

    public static function missing(SearchDocumentId $documentId): self
    {
        return new self($documentId, SearchOwnerReadStatus::Missing, null);
    }

    public static function corrupted(SearchDocumentId $documentId): self
    {
        return new self($documentId, SearchOwnerReadStatus::Corrupted, null);
    }

    public static function dependencyUnavailable(SearchDocumentId $documentId): self
    {
        return new self($documentId, SearchOwnerReadStatus::DependencyUnavailable, null);
    }
}
