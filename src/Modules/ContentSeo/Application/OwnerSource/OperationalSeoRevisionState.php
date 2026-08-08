<?php

namespace Appart\Modules\ContentSeo\Application\OwnerSource;

use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;
use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoStatusV1;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class OperationalSeoRevisionState
{
    public string $resourceKey;

    public DateTimeImmutable $effectiveAt;

    public DateTimeImmutable $recordedAt;

    public function __construct(
        ContentSeoPublicResourceKey|string $resource,
        public int $revision,
        public OperationalSeoStatusV1 $decision,
        DateTimeImmutable $effectiveAt,
        DateTimeImmutable $recordedAt,
    ) {
        if ($revision < 1 || ! in_array($decision, [OperationalSeoStatusV1::Indexable, OperationalSeoStatusV1::NoIndex], true)) {
            throw new InvalidArgumentException('Operational SEO revision is invalid.');
        }
        $this->resourceKey = $resource instanceof ContentSeoPublicResourceKey ? $resource->canonical() : (new ContentSeoPublicResourceKey($resource))->canonical();
        $utc = new DateTimeZone('UTC');
        $this->effectiveAt = $effectiveAt->setTimezone($utc);
        $this->recordedAt = $recordedAt->setTimezone($utc);
        if ($this->recordedAt < $this->effectiveAt) {
            throw new InvalidArgumentException('Operational SEO chronology is invalid.');
        }
    }
}
