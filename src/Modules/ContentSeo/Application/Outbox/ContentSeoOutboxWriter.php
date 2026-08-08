<?php

namespace Appart\Modules\ContentSeo\Application\Outbox;

use Appart\Modules\ContentSeo\Application\Delivery\EditorialContentDeliveryV1;
use Appart\Modules\ContentSeo\Application\Delivery\OperationalSeoDeliveryV1;

interface ContentSeoOutboxWriter
{
    public function append(EditorialContentDeliveryV1|OperationalSeoDeliveryV1 $delivery): ContentSeoOutboxResult;
}
