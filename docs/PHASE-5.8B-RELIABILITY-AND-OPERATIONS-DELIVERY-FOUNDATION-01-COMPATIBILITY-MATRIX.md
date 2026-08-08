# Phase 5.8B — Reliability & Operations — Delivery Compatibility Matrix

| Surface | Autorisation | Garantie |
|---|---|---|
| Events V1 certifiés | Autorisée, source exclusive | Sept familles uniquement |
| Event Type | Conservé | Même instance enum |
| status | Conservé | Réduction exhaustive, mécanique, bijective et homonyme |
| observedAt | Conservé | Copie stricte sans transformation |
| Payload supplémentaire | Interdit | Payload limité à status et observedAt |
| Readers, Owner Source, Runtime, HTTP | Interdits | Aucune dépendance |
| PostgreSQL, migration, Infrastructure | Interdits | Migration 088 et rollback inchangés |
| Outbox, Transport, Routing, Consumer | Interdits | Aucune surface aval ouverte |

