# Listing Publication Consumer Compatibility Specification

`ListingPublicationEventDeliveryConsumer` :

1. exige `ListingPublicationDeliveryPayload` ;
2. restaure l'événement canonique ;
3. vérifie type, version, source, aggregate, identité, ordre et métadonnées de l'enveloppe ;
4. appelle exactement une fois `ListingPublicationEventRouter` ;
5. traduit exclusivement son statut fermé.

Une incohérence de transport devient `DivergentPayload` avant tout routage. Le Consumer ne recalcule ni transition ni événement et n'appelle aucune Projection.
