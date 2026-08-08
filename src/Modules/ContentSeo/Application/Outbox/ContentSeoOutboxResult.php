<?php

namespace Appart\Modules\ContentSeo\Application\Outbox;

use Appart\Modules\ContentSeo\Application\Delivery\EditorialContentDeliveryV1;
use Appart\Modules\ContentSeo\Application\Delivery\OperationalSeoDeliveryV1;

final readonly class ContentSeoOutboxResult
{
    public function __construct(
        public string $messageId,
        public ContentSeoOutboxStatus $status,
        public EditorialContentDeliveryV1|OperationalSeoDeliveryV1 $delivery,
        public int $retryCount,
    ) {}
}
