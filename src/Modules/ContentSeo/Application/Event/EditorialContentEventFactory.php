<?php

namespace Appart\Modules\ContentSeo\Application\Event;

use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoObservedAt;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;
use Appart\Modules\ContentSeo\Application\PublicRead\Contract\EditorialContentReaderV1;
use Appart\Modules\ContentSeo\Application\PublicRead\EditorialContentStatusV1;

final readonly class EditorialContentEventFactory
{
    public function __construct(private EditorialContentReaderV1 $reader) {}

    public function create(ContentSeoPublicResourceKey $resource, ContentSeoObservedAt $observedAt): EditorialContentEventV1
    {
        $status = match ($this->reader->read($resource, $observedAt)->status) {
            EditorialContentStatusV1::Published => EditorialContentEventStatus::Published,
            EditorialContentStatusV1::Unpublished => EditorialContentEventStatus::Unpublished,
            EditorialContentStatusV1::Missing => EditorialContentEventStatus::Missing,
            EditorialContentStatusV1::Corrupted => EditorialContentEventStatus::Corrupted,
            EditorialContentStatusV1::DependencyUnavailable => EditorialContentEventStatus::DependencyUnavailable,
        };

        return new EditorialContentEventV1(EditorialContentEventType::Observed, new EditorialContentEventPayload($status, $observedAt->canonical()));
    }
}
