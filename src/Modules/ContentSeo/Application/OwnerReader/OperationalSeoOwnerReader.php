<?php

namespace Appart\Modules\ContentSeo\Application\OwnerReader;

use Appart\Modules\ContentSeo\Application\OwnerReader\Contract\ContentSeoOwnerReaderV1;
use Appart\Modules\ContentSeo\Application\OwnerSource\ContentSeoOwnerSource;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoObservedAt;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;
use Appart\Modules\ContentSeo\Application\PublicRead\Contract\OperationalSeoReaderV1;
use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoResultV1;

final readonly class OperationalSeoOwnerReader implements OperationalSeoReaderV1
{
    public function __construct(private ContentSeoOwnerSource $source, private ContentSeoOwnerReaderV1 $policy) {}

    public function read(ContentSeoPublicResourceKey $resource, ContentSeoObservedAt $observedAt): OperationalSeoResultV1
    {
        return match ($this->policy->operational($this->source->readOperationalSeo($resource, $observedAt))->status) {
            ContentSeoOwnerReaderStatus::Indexable => OperationalSeoResultV1::indexable(),
            ContentSeoOwnerReaderStatus::NoIndex => OperationalSeoResultV1::noIndex(),
            ContentSeoOwnerReaderStatus::Missing => OperationalSeoResultV1::missing(),
            ContentSeoOwnerReaderStatus::Corrupted => OperationalSeoResultV1::corrupted(),
            ContentSeoOwnerReaderStatus::DependencyUnavailable => OperationalSeoResultV1::dependencyUnavailable(),
            ContentSeoOwnerReaderStatus::Published,
            ContentSeoOwnerReaderStatus::Unpublished => throw new \LogicException('Invalid Operational SEO owner status.'),
        };
    }
}
