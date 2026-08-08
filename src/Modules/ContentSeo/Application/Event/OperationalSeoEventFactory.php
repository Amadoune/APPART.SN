<?php

namespace Appart\Modules\ContentSeo\Application\Event;

use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoObservedAt;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;
use Appart\Modules\ContentSeo\Application\PublicRead\Contract\OperationalSeoReaderV1;
use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoStatusV1;

final readonly class OperationalSeoEventFactory
{
    public function __construct(private OperationalSeoReaderV1 $reader) {}

    public function create(ContentSeoPublicResourceKey $resource, ContentSeoObservedAt $observedAt): OperationalSeoEventV1
    {
        $status = match ($this->reader->read($resource, $observedAt)->status) {
            OperationalSeoStatusV1::Indexable => OperationalSeoEventStatus::Indexable,
            OperationalSeoStatusV1::NoIndex => OperationalSeoEventStatus::NoIndex,
            OperationalSeoStatusV1::Missing => OperationalSeoEventStatus::Missing,
            OperationalSeoStatusV1::Corrupted => OperationalSeoEventStatus::Corrupted,
            OperationalSeoStatusV1::DependencyUnavailable => OperationalSeoEventStatus::DependencyUnavailable,
        };

        return new OperationalSeoEventV1(OperationalSeoEventType::Observed, new OperationalSeoEventPayload($status, $observedAt->canonical()));
    }
}
