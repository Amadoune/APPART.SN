# Phase 5.8B — Reliability & Operations — HTTP Matrix

| Statut public | HTTP |
|---|---:|
| Available et tout statut Found homonyme | 200 |
| Missing | 404 |
| Corrupted | 503 |
| DependencyUnavailable | 503 |

Les statuts Found explicitement énumérés vers 200 sont : Available, Degraded, Healthy, Unavailable, Ready, AtRisk, Blocked, Sufficient et Exhausted. Aucun `default` ni fallback n'est utilisé.

Routes : `/api/reliability-operations/{observability|service-health|alerting|maintenance-operations|continuity|capacity-planning|operational-readiness}`.
