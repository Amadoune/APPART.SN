<?php

namespace App\Application\PublicProjectionWorker;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use InvalidArgumentException;

final readonly class PublicProjectionDeliveryConsumerRegistry
{
    /** @var list<PublicProjectionDeliveryConsumerRegistration> */
    private array $registrations;

    /** @param list<PublicProjectionDeliveryConsumerRegistration> $registrations */
    public function __construct(array $registrations)
    {
        $keys = [];
        foreach ($registrations as $registration) {
            $key = $this->key($registration->consumerId, $registration->eventType->value, $registration->payloadVersion->value);
            if (isset($keys[$key])) {
                throw new InvalidArgumentException("Duplicate consumer registration: {$key}");
            }
            $keys[$key] = true;
        }
        $this->registrations = $registrations;
    }

    public function resolve(PublicProjectionOutboxConsumerId $consumerId, PublicProjectionDeliveryMessage $message): PublicProjectionDeliveryConsumerResolution
    {
        $typeFound = false;
        foreach ($this->registrations as $registration) {
            if ($registration->consumerId->value !== $consumerId->value || $registration->eventType->value !== $message->eventType->value) {
                continue;
            }
            $typeFound = true;
            if ($registration->payloadVersion->value === $message->payloadVersion->value) {
                return PublicProjectionDeliveryConsumerResolution::found(
                    $registration->consumer,
                    $registration->mode,
                );
            }
        }

        return $typeFound ? PublicProjectionDeliveryConsumerResolution::unsupportedVersion() : PublicProjectionDeliveryConsumerResolution::unsupportedType();
    }

    private function key(PublicProjectionOutboxConsumerId $consumer, string $eventType, int $version): string
    {
        return "{$consumer->value}:{$eventType}:{$version}";
    }
}
