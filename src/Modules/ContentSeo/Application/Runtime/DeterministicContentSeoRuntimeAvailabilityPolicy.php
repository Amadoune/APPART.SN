<?php

namespace Appart\Modules\ContentSeo\Application\Runtime;

use Appart\Modules\ContentSeo\Application\OwnerSource\ContentSeoOwnerSource;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoObservedAt;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;
use Appart\Modules\ContentSeo\Application\PublicRead\EditorialContentStatusV1;
use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoStatusV1;
use DateTimeImmutable;
use Throwable;

final readonly class DeterministicContentSeoRuntimeAvailabilityPolicy implements ContentSeoRuntimeAvailabilityPolicy
{
    private const PROBE_RESOURCE = 'runtime/content-seo-owner-source';

    public function __construct(private ContentSeoOwnerSource $source) {}

    public function inspect(): ContentSeoRuntimeAvailability
    {
        try {
            $resource = new ContentSeoPublicResourceKey(self::PROBE_RESOURCE);
            $observedAt = new ContentSeoObservedAt(new DateTimeImmutable('9999-12-31T23:59:59.999999Z'));
            $editorial = $this->source->readEditorial($resource, $observedAt);
            $seo = $this->source->readOperationalSeo($resource, $observedAt);

            if ($editorial->status === EditorialContentStatusV1::DependencyUnavailable
                || $seo->status === OperationalSeoStatusV1::DependencyUnavailable) {
                return ContentSeoRuntimeAvailability::DependencyUnavailable;
            }
            if ($editorial->status === EditorialContentStatusV1::Corrupted
                || $seo->status === OperationalSeoStatusV1::Corrupted) {
                return ContentSeoRuntimeAvailability::Corrupted;
            }

            return ContentSeoRuntimeAvailability::Available;
        } catch (Throwable) {
            return ContentSeoRuntimeAvailability::DependencyUnavailable;
        }
    }
}
