<?php

namespace App\Application\ModerationEventTransport;

use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;

final readonly class ModerationDeliveryMessageV1
{
    public string $messageId;

    public function __construct(public ModerationEventV1 $event)
    {
        $this->messageId = 'moderation-v1-'.hash('sha256', $event->eventId."\n".$event->checksum);
    }

    /** @return array<string, mixed> */
    public function fields(): array
    {
        return [
            'messageId' => $this->messageId, 'transportVersion' => 1,
            'payload' => $this->event->contract(),
            'metadata' => ['source' => 'ModerationReports', 'eventId' => $this->event->eventId, 'checksum' => $this->event->checksum],
        ];
    }
}
