<?php

namespace Appart\Modules\ContentSeo\Application\Delivery;

final readonly class OperationalSeoDeliveryV1
{
    public const TYPE = 'content_seo.operational_seo.delivery.v1';

    public function __construct(public OperationalSeoDeliveryPayload $payload) {}
}
