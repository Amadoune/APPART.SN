<?php

namespace App\Application\IdentityAccessEventTransport;

use Appart\Modules\IdentityAccess\Application\IdentityAccessEvent\IdentityAccessEventV1;

final readonly class IdentityAccessDeliveryMessageV1
{
    public string $messageId;

    public string $payloadChecksum;

    public function __construct(public IdentityAccessEventV1 $event)
    {
        $this->payloadChecksum = hash('sha256', self::canonicalJson($event->contract()));
        $this->messageId = 'iam-v1-'.hash('sha256', "1\n".$this->payloadChecksum);
    }

    /** @return array<string, array<string, int|string>|int|string> */
    public function fields(): array
    {
        return [
            'messageId' => $this->messageId,
            'messageType' => $this->event->type->value,
            'transportVersion' => 1,
            'payload' => $this->event->contract(),
            'metadata' => [
                'source' => 'IdentityAccess',
                'eventId' => $this->event->eventId,
                'payloadChecksum' => $this->payloadChecksum,
            ],
        ];
    }

    /** @param array<string, int|string> $value */
    private static function canonicalJson(array $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
