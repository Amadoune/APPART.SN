# Phase 5.8C — Experience & Acceptance — Temporal Read Matrix

| Cas | Résultat |
|---|---|
| Aucune révision au temps observé | Missing |
| Révision applicable et checksum valide | Found |
| Ligne ou chronologie invalide | Corrupted |
| Dépendance PostgreSQL indisponible | DependencyUnavailable |

La sélection est bornée par `effective_at <= observedAt` et `recorded_at <= observedAt`, puis ordonnée de manière déterministe par effectiveAt, recordedAt et revision décroissants.

