<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Transport;

use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxEventId;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessage;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessageId;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessageType;
use JsonException;

final readonly class DeterministicExperienceAcceptanceTransport implements ExperienceAcceptanceTransportV1
{
    public function serialize(ExperienceAcceptanceOutboxMessage $message): string
    {
        try {
            return json_encode(ExperienceAcceptanceTransportEnvelope::fromMessage($message)->canonical(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (JsonException $error) {
            throw new ExperienceAcceptanceTransportException('ExperienceAcceptance transport serialization failed.', previous: $error);
        }
    }

    public function deserialize(string $serialized): ExperienceAcceptanceTransportEnvelope
    {
        try {
            $data = json_decode($serialized, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $error) {
            throw new ExperienceAcceptanceTransportException('ExperienceAcceptance transport deserialization failed.', previous: $error);
        }
        if (! is_array($data) || array_keys($data) !== ['messageId', 'eventId', 'type', 'status', 'observedAt', 'checksum']) {
            throw new ExperienceAcceptanceTransportException('Invalid ExperienceAcceptance transport envelope.');
        }
        foreach ($data as $value) {
            if (! is_string($value)) {
                throw new ExperienceAcceptanceTransportException('Invalid ExperienceAcceptance transport field.');
            }
        }
        $envelope = new ExperienceAcceptanceTransportEnvelope(new ExperienceAcceptanceOutboxMessageId($data['messageId']), new ExperienceAcceptanceOutboxEventId($data['eventId']), ExperienceAcceptanceOutboxMessageType::from($data['type']), $data['status'], $data['observedAt'], $data['checksum']);
        $expected = hash('sha256', implode("\n", ['experience-acceptance-outbox-v1', ExperienceAcceptanceOutboxMessage::OWNER, (string) ExperienceAcceptanceOutboxMessage::SCHEMA_VERSION, $envelope->eventId->value, $envelope->type->value, $envelope->status, $envelope->observedAt]));
        if (! hash_equals($expected, $envelope->checksum)) {
            throw new ExperienceAcceptanceTransportException('ExperienceAcceptance transport checksum mismatch.');
        }

        return $envelope;
    }
}
