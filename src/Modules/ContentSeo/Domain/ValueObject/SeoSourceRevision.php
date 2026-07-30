<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;
use DateTimeImmutable;

final readonly class SeoSourceRevision
{
    private function __construct(public SeoSourceKind $source, public int $version, public string $factId, public string $coherenceId, public DateTimeImmutable $effectiveAt) {}

    public static function create(SeoSourceKind $source, int $version, string $factId, string $coherenceId, DateTimeImmutable $effectiveAt): self
    {
        $factId = strtolower(trim($factId));
        $coherenceId = strtolower(trim($coherenceId));
        $uuid = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
        if ($version < 1 || preg_match($uuid, $factId) !== 1 || preg_match($uuid, $coherenceId) !== 1) {
            throw InvalidSeoValue::field('source_revision');
        }

        return new self($source, $version, $factId, $coherenceId, $effectiveAt);
    }

    public function identicalTo(self $other): bool
    {
        return $this->source === $other->source && $this->version === $other->version && $this->factId === $other->factId && $this->coherenceId === $other->coherenceId && $this->effectiveAt == $other->effectiveAt;
    }
}
