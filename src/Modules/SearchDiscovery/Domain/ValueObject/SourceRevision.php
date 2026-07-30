<?php

namespace Appart\Modules\SearchDiscovery\Domain\ValueObject;

use Appart\Modules\SearchDiscovery\Domain\Exception\InvalidSearchValue;
use DateTimeImmutable;

final readonly class SourceRevision
{
    private function __construct(
        public SourceKind $source,
        public int $version,
        public string $factId,
        public DateTimeImmutable $effectiveAt,
    ) {}

    public static function create(SourceKind $source, int $version, string $factId, DateTimeImmutable $effectiveAt): self
    {
        $factId = strtolower(trim($factId));
        if ($version < 1 || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $factId) !== 1) {
            throw InvalidSearchValue::field('source_revision');
        }

        return new self($source, $version, $factId, $effectiveAt);
    }

    public function sameFact(self $other): bool
    {
        return $this->source === $other->source
            && $this->version === $other->version
            && $this->factId === $other->factId
            && $this->effectiveAt == $other->effectiveAt;
    }
}
