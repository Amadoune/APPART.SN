# Provider Bindings

| Concret | Abstraction | Portée |
|---|---|---|
| PostgreSqlAdministrationConsoleOwnerSource | AdministrationConsoleOwnerSource | singleton, lazy |
| DeterministicAdministrationConsoleRuntimeAvailabilityPolicy | AdministrationConsoleRuntimeAvailabilityPolicy | singleton, lazy |
| DeterministicAdministrationConsoleRuntime | AdministrationConsoleRuntimeV1 | singleton, lazy |

Chaque alias est nominatif et unique. Le mapper est également singleton.
