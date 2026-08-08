<?php

namespace Appart\Modules\ContentSeo\Application\OwnerReader;

use Appart\Modules\ContentSeo\Application\OwnerReader\Contract\ContentSeoOwnerReaderV1;
use Appart\Modules\ContentSeo\Application\OwnerSource\ContentSeoOwnerSource;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoObservedAt;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;
use Appart\Modules\ContentSeo\Application\PublicRead\Contract\EditorialContentReaderV1;
use Appart\Modules\ContentSeo\Application\PublicRead\EditorialContentResultV1;

final readonly class EditorialContentOwnerReader implements EditorialContentReaderV1
{
    public function __construct(private ContentSeoOwnerSource $source, private ContentSeoOwnerReaderV1 $policy) {}

    public function read(ContentSeoPublicResourceKey $resource, ContentSeoObservedAt $observedAt): EditorialContentResultV1
    {
        return match ($this->policy->editorial($this->source->readEditorial($resource, $observedAt))->status) {
            ContentSeoOwnerReaderStatus::Published => EditorialContentResultV1::published(),
            ContentSeoOwnerReaderStatus::Unpublished => EditorialContentResultV1::unpublished(),
            ContentSeoOwnerReaderStatus::Missing => EditorialContentResultV1::missing(),
            ContentSeoOwnerReaderStatus::Corrupted => EditorialContentResultV1::corrupted(),
            ContentSeoOwnerReaderStatus::DependencyUnavailable => EditorialContentResultV1::dependencyUnavailable(),
            ContentSeoOwnerReaderStatus::Indexable,
            ContentSeoOwnerReaderStatus::NoIndex => throw new \LogicException('Invalid Editorial Content owner status.'),
        };
    }
}
