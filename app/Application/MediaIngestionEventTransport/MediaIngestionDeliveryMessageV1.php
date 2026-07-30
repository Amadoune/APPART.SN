<?php

namespace App\Application\MediaIngestionEventTransport;

use Appart\Modules\Media\Application\MediaIngestionEvent\MediaIngestionEventV1;

final readonly class MediaIngestionDeliveryMessageV1
{
    public string $messageId;

    public string $payloadChecksum;

    public function __construct(public MediaIngestionEventV1 $event)
    {
        $this->payloadChecksum = hash('sha256', self::canonicalJson($event->contract()));
        $this->messageId = 'media-ingestion-v1-'.hash('sha256', "1\n".$this->payloadChecksum);
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
                'source' => 'MediaIngestion',
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
