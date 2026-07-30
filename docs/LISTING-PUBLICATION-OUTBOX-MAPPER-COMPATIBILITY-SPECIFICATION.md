# Listing Publication Outbox Mapper Compatibility Specification

## Écriture

Le mapper historique utilise sans modification le contrat `PublicProjectionDeliveryPayload::fields`. Pour Listing Publication, le JSON stocké contient uniquement `canonicalEvent`, avec la chaîne canonique 4.1EA exacte. Le checksum stocké est celui du payload 4.1EBA.

## Lecture

Lorsque `event_type` appartient à `ListingPublicationEventType`, le mapper délègue à `ListingPublicationDeliveryPayload::restore`. Cette restauration vérifie l'enveloppe, l'identité métier et l'égalité byte-for-byte.

`message_id`, `idempotency_key`, `eventId`, métadonnées, ordre causal et checksum sont conservés séparément selon leurs propriétaires.

Les cinq branches historiques du mapper restent inchangées.
