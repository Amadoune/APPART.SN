# Phase 5.8B — Reliability & Operations — Outbox Compatibility Matrix

| Surface | État | Garantie |
|---|---|---|
| Sept Deliveries V1 | Sources exclusives | Aucun accès Reader, Event direct, Runtime ou HTTP |
| Journal 089 | Additif, append-only | Identité et checksum SHA-256 canoniques |
| État technique | Mutable séparé du journal | claim, retry et épuisement borné |
| Concurrence | Compatible | FOR UPDATE SKIP LOCKED et idempotence stricte |
| Transactions externes | Préservées | Savepoints locaux, aucun commit implicite |
| Migrations 084–088 | Inchangées | Empreintes protégées par Architecture |
| Transport, Routing, Consumer | Non ouverts | Aucune dépendance ou implémentation |

