# Initial handoff evidence

`ListingPublicationEventDeliveryConsumer` appelle le materializer V2 exclusivement pour ListingPublished, avec ListingId seulement. Les résultats sont réduits vers Consumed, BlockedBySourceReadiness, RetryableFailure ou PermanentFailure avant les autres materializations.
