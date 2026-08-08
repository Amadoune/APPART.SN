<?php

namespace Appart\Modules\ContentSeo\Application\Outbox;

use Appart\Modules\ContentSeo\Application\Delivery\EditorialContentDeliveryV1;
use Appart\Modules\ContentSeo\Application\Delivery\OperationalSeoDeliveryV1;

final readonly class ContentSeoOutboxPolicy
{
    public const MAX_RETRIES = 10;

    public function prepare(EditorialContentDeliveryV1|OperationalSeoDeliveryV1 $delivery): ContentSeoOutboxResult
    {
        return new ContentSeoOutboxResult($this->messageId($delivery), ContentSeoOutboxStatus::Applied, $delivery, 0);
    }

    public function messageId(EditorialContentDeliveryV1|OperationalSeoDeliveryV1 $delivery): string
    {
        return hash('sha256', $this->canonical($delivery));
    }

    public function checksum(EditorialContentDeliveryV1|OperationalSeoDeliveryV1 $delivery): string
    {
        return hash('sha256', "content-seo-outbox-v1\n".$this->canonical($delivery));
    }

    public function canonical(EditorialContentDeliveryV1|OperationalSeoDeliveryV1 $delivery): string
    {
        return json_encode($delivery->payload->canonical(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }
}
