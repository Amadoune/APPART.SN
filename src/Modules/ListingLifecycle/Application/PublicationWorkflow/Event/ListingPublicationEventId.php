<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event;

final readonly class ListingPublicationEventId
{
    private function __construct(public string $value) {}

    public static function derive(ListingPublicationEventType $type, ListingPublicationEventPayloadVersion $payloadVersion, ListingPublicationEventPayload $payload): self
    {
        $identity = implode('|', [
            $payload->listingId->value,
            (string) $payload->publicationVersion,
            $type->value,
            (string) $payloadVersion->value,
        ]);

        return new self('listing-publication-'.hash('sha256', $identity));
    }
}
