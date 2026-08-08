# Outbox Matrix — Legacy Migration

| Source Delivery | Type conservé | Résultat d'écriture |
|---|---|---|
| Inventory | `legacy-migration.inventory.observed.v1` | Applied / AlreadyApplied / DivergentMessage / DependencyUnavailable |
| Wave | `legacy-migration.wave.observed.v1` | Applied / AlreadyApplied / DivergentMessage / DependencyUnavailable |
| Reconciliation | `legacy-migration.reconciliation.observed.v1` | Applied / AlreadyApplied / DivergentMessage / DependencyUnavailable |
| Quarantine | `legacy-migration.quarantine.observed.v1` | Applied / AlreadyApplied / DivergentMessage / DependencyUnavailable |
| Cutover | `legacy-migration.cutover.observed.v1` | Applied / AlreadyApplied / DivergentMessage / DependencyUnavailable |

Le JSON canonique ordonne `type`, `status`, `observedAt`. `messageId` est le SHA-256 déterministe du JSON ; le checksum est le SHA-256 du JSON préfixé par la version d'Outbox.

La lecture sélectionne les messages non délivrés avec `retry_count < 10`, ordonnés par `created_at,message_id`, et reconstruit `observedAt` en UTC canonique.
