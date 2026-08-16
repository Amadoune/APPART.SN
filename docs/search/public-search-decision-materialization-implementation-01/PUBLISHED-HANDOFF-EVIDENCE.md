# Published Handoff Evidence

Après routage réussi d’un événement `ListingPublished`, `ListingPublicationEventDeliveryConsumer` appelle `MaterializePublicSearchDecisionV1`.

Réduction fermée :

- `Applied`, `AlreadyApplied`, `RejectedObsolete` → consumed ;
- `SourceMissing` → blocked by source readiness ;
- `DependencyUnavailable` → retryable failure ;
- `SourceCorrupted`, `Divergent` → permanent failure.

Les autres événements Listing restent consommés sans matérialisation Search. Les tests couvrent le déclenchement et chaque réduction.
