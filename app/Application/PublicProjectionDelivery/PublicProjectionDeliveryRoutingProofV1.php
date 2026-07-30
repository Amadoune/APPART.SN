<?php

namespace App\Application\PublicProjectionDelivery;

use InvalidArgumentException;
use JsonException;

final readonly class PublicProjectionDeliveryRoutingProofV1
{
    public const VERSION = 1;

    public function __construct(
        public PublicProjectionDeliveryMessageId $messageId,
        public PublicProjectionDeliverySourceModule $sourceModule,
        public PublicProjectionDeliveryEventType $eventType,
        public PublicProjectionDeliveryDestination $destination,
        public int $routingVersion,
        public string $checksum,
    ) {
        if ($routingVersion !== self::VERSION || preg_match('/^[a-f0-9]{64}$/D', $checksum) !== 1) {
            throw new InvalidArgumentException('Public Projection routing proof shape is invalid.');
        }
    }

    public static function issue(
        PublicProjectionDeliveryMessage $message,
        PublicProjectionDeliveryDestination $destination,
    ): self {
        return new self(
            $message->messageId,
            $message->sourceModule,
            $message->eventType,
            $destination,
            self::VERSION,
            self::checksumFor(
                $message->messageId,
                $message->sourceModule,
                $message->eventType,
                $destination,
                self::VERSION,
            ),
        );
    }

    public function isValidFor(
        PublicProjectionDeliveryMessage $message,
        PublicProjectionDeliveryDestination $destination,
    ): bool {
        return $this->messageId == $message->messageId
            && $this->sourceModule == $message->sourceModule
            && $this->eventType == $message->eventType
            && $this->destination == $destination
            && $this->routingVersion === self::VERSION
            && hash_equals(
                self::checksumFor(
                    $this->messageId,
                    $this->sourceModule,
                    $this->eventType,
                    $this->destination,
                    $this->routingVersion,
                ),
                $this->checksum,
            );
    }

    private static function checksumFor(
        PublicProjectionDeliveryMessageId $messageId,
        PublicProjectionDeliverySourceModule $sourceModule,
        PublicProjectionDeliveryEventType $eventType,
        PublicProjectionDeliveryDestination $destination,
        int $routingVersion,
    ): string {
        try {
            $canonical = json_encode([
                'messageId' => $messageId->value,
                'sourceModule' => $sourceModule->value,
                'eventType' => $eventType->value,
                'destination' => $destination->value,
                'routingVersion' => $routingVersion,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $error) {
            throw new InvalidArgumentException('Public Projection routing proof cannot be encoded.', previous: $error);
        }

        return hash('sha256', $canonical);
    }
}
