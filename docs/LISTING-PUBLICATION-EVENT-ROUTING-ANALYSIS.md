# Listing Publication Event Routing Analysis

## Décision

Le routage de production utilise une Inbox PostgreSQL durable propriétaire de Listing Lifecycle. Cette destination fournit une preuve de transfert indépendante de tout handler métier futur et évite tout acquittement optimiste.

## Responsabilités

`DurableListingPublicationEventRouter` délègue l'événement inchangé à `ListingPublicationEventDestination`. Il ne sérialise pas, ne choisit pas de transition et ne connaît ni HTTP ni Projection.

`PostgreSqlListingPublicationEventInbox` sérialise avec le serializer certifié 4.1EA, calcule l'intégrité technique, insère l'enveloppe ou confirme son existence strictement identique. Seuls ces deux résultats deviennent `Routed`.

## Reprise

Les événements reçus portent le statut `pending` et zéro tentative. L'index partiel `(status, inbox_id)` fournit un ordre stable pour une future capacité de reprise, hors périmètre de 4.1EBR.
