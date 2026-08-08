# Phase 5.8B — Reliability & Operations — Provider Bindings

`ReliabilityOperationsRuntimeServiceProvider` est enregistré une seule fois dans `bootstrap/providers.php`.

| Concret singleton lazy | Alias nominatif unique |
|---|---|
| PostgreSqlReliabilityOperationsOwnerSource | ReliabilityOperationsOwnerSource |
| DeterministicReliabilityOperationsRuntimeAvailabilityPolicy | ReliabilityOperationsRuntimeAvailabilityPolicy |
| DeterministicReliabilityOperationsRuntime | ReliabilityOperationsRuntimeV1 |

Le mapper est également singleton. Aucun autre Provider ou binding n'est créé.
