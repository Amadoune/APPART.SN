# Administration Console Outbox Foundation — Outbox Matrix

| Entrée | Event Type conservé | Statuts acceptés | Sortie |
|---|---|---|---|
| `AdministrationOperatorDeliveryV1` | `administration_console.operator.observed.v1` | Available, Unavailable, Missing, Corrupted, DependencyUnavailable | Un message Outbox |
| `AdministrationQueueDeliveryV1` | `administration_console.queue.observed.v1` | Ready, Empty, Missing, Corrupted, DependencyUnavailable | Un message Outbox |
| `AdministrationAuditDeliveryV1` | `administration_console.audit.observed.v1` | Available, Missing, Corrupted, DependencyUnavailable | Un message Outbox |

| Situation d'append | Status Outbox |
|---|---|
| Message absent | `Applied` |
| Même identité, même payload et même checksum | `AlreadyApplied` |
| Même identité, payload ou checksum divergent | `DivergentMessage` |
| Erreur de dépendance PostgreSQL | `DependencyUnavailable` |

Le message ID est `SHA-256(canonical)`. Le checksum est `SHA-256("administration-console-outbox-v1\n" + canonical)`. Aucune autre source ou réduction n'intervient.
