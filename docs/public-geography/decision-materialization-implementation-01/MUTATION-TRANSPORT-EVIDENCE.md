# Mutation transport evidence

Enable/Disable/Merge réutilisent `PlaceLifecycleDeliveryConsumer`, désormais relié au refresh. Rename utilise `RenamePlaceWithPublicGeographyHandoff` dans la transaction atomique Geography/outbox existante, avec eventId SHA-256 déterministe, placeId, aggregateVersion et occurredAt. Son consumer est enregistré sur `place.lifecycle.renamed`.
