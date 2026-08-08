# Phase 5.8C — Experience & Acceptance — Provider Bindings

| Service concret | Alias nominal |
|---|---|
| PostgreSqlExperienceAcceptanceOwnerSource | ExperienceAcceptanceOwnerSource |
| DeterministicExperienceAcceptanceRuntimeAvailabilityPolicy | ExperienceAcceptanceRuntimeAvailabilityPolicy |
| DeterministicExperienceAcceptanceRuntime | ExperienceAcceptanceRuntimeV1 |

Tous les bindings sont singleton et résolus de manière lazy. `ExperienceAcceptanceRuntimeServiceProvider` est enregistré une seule fois dans `bootstrap/providers.php`.

