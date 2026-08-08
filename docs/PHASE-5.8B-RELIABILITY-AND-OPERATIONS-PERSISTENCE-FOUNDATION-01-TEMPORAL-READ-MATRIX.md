# Phase 5.8B — Reliability & Operations — Temporal Read Matrix

| Situation | Résultat |
|---|---|
| aucune révision visible à observedAt | Missing |
| révision effective et enregistrée avant observedAt | Found |
| plusieurs révisions visibles | effectiveAt, recordedAt puis revision décroissants |
| checksum invalide | Corrupted |
| erreur PostgreSQL | DependencyUnavailable |

Une révision est visible uniquement si `effective_at <= observedAt` et `recorded_at <= observedAt`. L'index courant n'est jamais utilisé comme source de lecture temporelle.
