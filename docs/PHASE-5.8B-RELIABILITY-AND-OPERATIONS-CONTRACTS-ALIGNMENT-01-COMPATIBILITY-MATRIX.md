# Phase 5.8B — Reliability & Operations — Contracts Alignment Compatibility Matrix

| État source | Cible publique | Compatibilité |
|---|---|---|
| Found(decision) | StatusV1 homonyme du stream | exhaustive |
| Missing | Missing | bijective |
| Corrupted | Corrupted | bijective |
| DependencyUnavailable | DependencyUnavailable | bijective |

Les sept Readers V1 possèdent les quatre branches requises. Les Results restent limités à `status` et `observedAt`. Aucune dépendance Runtime, Persistence ou Infrastructure n'est ajoutée.
