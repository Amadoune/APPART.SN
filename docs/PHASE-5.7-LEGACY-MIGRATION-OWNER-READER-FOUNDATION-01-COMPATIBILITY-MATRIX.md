# Compatibility Matrix — Legacy Migration Owner Reader Foundation

| Surface | Dépendance autorisée | Dépendance interdite | Statut |
|---|---|---|---|
| cinq Owner Readers | `LegacyMigrationOwnerSource` + contrats V1 | Runtime, Infrastructure | Compatible |
| Policy commune | cinq Read Results owner-scoped | agrégation et décision métier | Compatible |
| Results publics | statut homonyme + `observedAt` | Revision State et données Legacy | Compatible |
| Provider | six singletons et six aliases nominatifs | alias ambigu | Compatible |
| migration 084 | aucune interaction | modification | Inchangée |

Discovery, Contracts, Persistence, Runtime et Boundary Audit demeurent fermés. Les capacités 5.1 à 5.6 restent finales, fermées et gelées.
