<?php

namespace App\Application\PublicProjectionWorker;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;

final readonly class PublicProjectionDeliveryMessageOutcome
{
    public function __construct(
        public PublicProjectionDeliveryMessageId $messageId,
        public PublicProjectionDeliveryOutcome $outcome,
        public ?string $technicalCode = null,
    ) {}
}
