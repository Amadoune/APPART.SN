<?php

namespace App\Infrastructure\PublicProjectionStore\PostgreSql;

use App\Application\Contract\PublicListingQuery;
use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionWriteResult;
use App\ReadModels\PublicListingReadModel;

final readonly class PostgreSqlPublicListingProjectionStore implements PublicListingProjectionWriter, PublicListingQuery
{
    public function __construct(private PostgreSqlPublicListingProjectionWriter $writer, private PostgreSqlPublicListingProjectionReader $reader) {}

    public function applyCurrent(PublicListingProjectionRecord $record): PublicProjectionWriteResult
    {
        return $this->writer->applyCurrent($record);
    }

    public function replaceCanonical(string $previousCanonicalPath, PublicListingProjectionRecord $replacement): PublicProjectionWriteResult
    {
        return $this->writer->replaceCanonical($previousCanonicalPath, $replacement);
    }

    public function applyTombstone(PublicListingProjectionRecord $tombstone): PublicProjectionWriteResult
    {
        return $this->writer->applyTombstone($tombstone);
    }

    public function writeCandidate(PublicListingProjectionRecord $record): PublicProjectionWriteResult
    {
        return $this->writer->writeCandidate($record);
    }

    public function findByCanonicalPath(string $canonicalPath): ?PublicListingReadModel
    {
        return $this->reader->findByCanonicalPath($canonicalPath);
    }
}
