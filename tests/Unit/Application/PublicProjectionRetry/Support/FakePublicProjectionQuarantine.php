<?php

namespace Tests\Unit\Application\PublicProjectionRetry\Support;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;

final class FakePublicProjectionQuarantine
{
    /** @var array<string, true> */
    public array $released = [];

    public function release(PublicProjectionDeliveryMessageId $messageId): void
    {
        $this->released[$messageId->value] = true;
    }
}
