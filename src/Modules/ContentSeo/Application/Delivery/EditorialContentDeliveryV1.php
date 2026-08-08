<?php

namespace Appart\Modules\ContentSeo\Application\Delivery;

final readonly class EditorialContentDeliveryV1
{
    public const TYPE = 'content_seo.editorial_content.delivery.v1';

    public function __construct(public EditorialContentDeliveryPayload $payload) {}
}
