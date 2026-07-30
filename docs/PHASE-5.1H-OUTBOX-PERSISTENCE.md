# Phase 5.1H — Outbox Persistence

Le message persiste le transport canonique 5.1G, son event ID, type, compte, version, checksum et ses dates. Il ne persiste aucune PII additionnelle.

Chaque destination possède un état indépendant :

`pending → claimed → delivered`

ou :

`claimed → retry_scheduled → claimed`

ou :

`claimed → quarantined`.

La clé primaire du message et l'unicité de l'event ID empêchent les doublons. La clé `(message_id, destination)` garantit exactement une ligne de livraison par destination. Un replay byte-identique retourne `AlreadyApplied`; toute divergence retourne `DivergentMessage`.
