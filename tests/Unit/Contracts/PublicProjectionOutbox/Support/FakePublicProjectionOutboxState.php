<?php

namespace Tests\Unit\Contracts\PublicProjectionOutbox\Support;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRecord;

final class FakePublicProjectionOutboxState
{
    /** @var array<string, PublicProjectionOutboxRecord> */
    public array $records = [];

    public function key(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumer): string
    {
        return $consumer->value.'|'.$message->idempotencyKey->value;
    }

    public function find(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumer): ?PublicProjectionOutboxRecord
    {
        return $this->records[$this->key($message, $consumer)] ?? null;
    }

    public function put(PublicProjectionOutboxRecord $record): void
    {
        $this->records[$this->key($record->message, $record->consumerId)] = $record;
    }
}
