# Administration Console Owner Reader — Dependency Matrix

| Composant futur | Dépendance source autorisée | Cible autorisée | Dépendances interdites |
|---|---|---|---|
| `AdministrationOperatorOwnerReader` | `AdministrationConsoleOwnerSource` | `AdministrationOperatorReaderV1` et ses types V1 | Toute autre source, Runtime, Runtime Read, Infrastructure, PostgreSQL, SQL, HTTP, Event, Delivery, Outbox |
| `AdministrationQueueOwnerReader` | `AdministrationConsoleOwnerSource` | `AdministrationQueueReaderV1` et ses types V1 | Toute autre source, Runtime, Runtime Read, Infrastructure, PostgreSQL, SQL, HTTP, Event, Delivery, Outbox |
| `AdministrationAuditOwnerReader` | `AdministrationConsoleOwnerSource` | `AdministrationAuditReaderV1` et ses types V1 | Toute autre source, Runtime, Runtime Read, Infrastructure, PostgreSQL, SQL, HTTP, Event, Delivery, Outbox |

## Autorisé

- appels read-only au port Application `AdministrationConsoleOwnerSource` ;
- transmission inchangée de `AdministrationSubjectKey` et `AdministrationObservedAt` ;
- construction du résultat V1 correspondant à partir du seul statut homonyme.

## Interdit

- Provider, binding ou composition Runtime ;
- accès direct à Persistence, à la migration 082, à SQL ou PostgreSQL ;
- fallback, agrégation, cache, projection, autre owner ou autre domaine ;
- Runtime Read, HTTP, Event, Delivery, Outbox, Transport, Routing ou Consumer ;
- modification des Foundations certifiées ou ajout d'un contrat.
