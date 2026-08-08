# Provider & Bindings — Legacy Migration Runtime

`LegacyMigrationRuntimeServiceProvider` est enregistré une seule fois dans `bootstrap/providers.php`.

| Abstraction | Implémentation nominative | Cycle |
|---|---|---|
| `LegacyMigrationOwnerSource` | `PostgreSqlLegacyMigrationOwnerSource` | singleton lazy |
| `LegacyMigrationRuntimeAvailabilityPolicy` | `DeterministicLegacyMigrationRuntimeAvailabilityPolicy` | singleton lazy |
| `LegacyMigrationRuntimeV1` | `DeterministicLegacyMigrationRuntime` | singleton lazy |

Le mapper et l'adapter PostgreSQL sont également des singletons nominatifs. Aucun alias ambigu et aucune autre source ne sont introduits.
