# Phase 5.3G — Event / Transport / Routing / Delivery

## Statut proposé

`GO PROPOSÉ`

Le catalogue V1 est fermé aux cinq types certifiés. Les événements sont
immutables, sans PII, avec `eventId` et checksum déterministes. Le transport
canonique refuse toute altération.

Le routing est statique et limité à trois destinations owner-local :
`moderation.queue`, `moderation.timeline` et
`moderation.delivery-observation`. Aucune destination externe n'est résolue.

La migration additive 065 crée uniquement le ledger entrant
`moderation_reports.event_deliveries`. Il ne s'agit pas d'une Outbox. Le ledger
garantit Delivery, retry borné, replay, quarantaine, divergence, advisory lock,
savepoint et rollback. Les migrations 063 et 064 restent inchangées.

La composition dépend uniquement de `ModerationRuntimeV1` et fail-closed si le
Runtime propriétaire n'est pas sain.

Les six amendements 5.3C, HTTP, handoff, Reader, Gateway, domaines externes et
Outbox restent hors périmètre.
