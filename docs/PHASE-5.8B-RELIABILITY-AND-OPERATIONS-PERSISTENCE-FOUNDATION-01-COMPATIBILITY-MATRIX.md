# Phase 5.8B — Reliability & Operations — Persistence Compatibility Matrix

| Garantie Contracts | Persistence | État |
|---|---|---|
| sept domaines publics | sept streams fermés | conforme |
| owner ReliabilityOperations | schéma et port owner-scoped | conforme |
| état minimal | décision fermée interne par stream | conforme |
| instant UTC canonique | effectiveAt et recordedAt canoniques | conforme |
| fail-closed | Corrupted et DependencyUnavailable | conforme |
| aucun payload public | journal sans payload libre | conforme |
| aucune dépendance gelée | aucune FK ni dépendance cross-owner | conforme |
| aucune surface aval | aucun Runtime, Provider, HTTP ou événement | conforme |
