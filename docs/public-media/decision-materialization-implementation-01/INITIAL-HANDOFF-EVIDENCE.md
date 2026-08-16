# Initial Handoff Evidence

Après routage certifié d'un `ListingPublished`, `ListingPublicationEventDeliveryConsumer` appelle `MaterializePublicMediaDecisionV2` avec le ListingId. Applied, replay et obsolete sont consommés ; source non prête bloque, panne dépendance reste retryable et corruption/divergence échoue définitivement.
