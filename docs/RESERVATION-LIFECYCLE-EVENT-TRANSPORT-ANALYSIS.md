# Reservation Lifecycle Event Transport Analysis

## Décision

Le sprint 4.3F introduit cinq contrats techniques dans `App\Application\ReservationLifecycleEventTransport`. Ils enveloppent l'événement métier 4.3E sans produire, router ou persister de message.

`ReservationLifecycleDeliveryPayload` conserve un unique champ `canonicalEvent`, égal octet pour octet à la sortie du serializer métier certifié. Sa restauration vérifie la forme, les types, l'identité et la canonicité; elle ne choisit ni ne transforme aucune valeur métier.

## Séparation des identités

- `eventId` reste l'identité métier 4.3E et demeure dans `canonicalEvent`.
- `businessEventId` en est une copie technique de corrélation, jamais une substitution.
- `messageId` est l'identité technique de transport, dérivée par SHA-256 des octets de `canonicalEvent` et préfixée par `reservation-lifecycle-delivery-`.
- Le serializer de transport ne calcule aucun `eventId`.

## Frontières

Le payload implémente le contrat Delivery commun déjà certifié afin de préparer une compatibilité future, sans utiliser Outbox, Worker ou Consumer. Le port `ReservationLifecycleEventRouterPort` reçoit uniquement l'enveloppe technique et ne possède aucune implémentation dans ce sprint.

Aucune horloge, valeur temporelle implicite, identité aléatoire, dépendance Laravel, PostgreSQL ou broker n'est introduite.
