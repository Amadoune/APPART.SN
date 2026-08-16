# Sequencing Matrix

| Ordre | Gate | Sortie requise |
|---:|---|---|
| 1 | RC2 predecessor immuable | Identité préservée |
| 2 | Build/CI Alignment Correction | Contrôles et guards alignés, aucune matérialisation |
| 3 | Successor Immutable Source Materialization | Commit unique + tag annoté successor |
| 4 | Packaging | Artifact et manifest déterministes |
| 5 | Reproducible Build | Restore et builds reproductibles |
| 6 | External CI | Exécution externe probante |
| 7 | Production Readiness | Décision indépendante |

Toute étape s'arrête à sa première divergence. Aucun gate aval ne peut être anticipé.
