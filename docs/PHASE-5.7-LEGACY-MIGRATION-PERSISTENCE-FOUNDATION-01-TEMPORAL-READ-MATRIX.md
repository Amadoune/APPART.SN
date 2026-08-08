# Temporal Read Matrix — Legacy Migration

| Condition à `observedAt` | Résultat |
|---|---|
| aucune révision admissible | Missing |
| dernière révision avec `effectiveAt <= observedAt` et `recordedAt <= observedAt` | Found |
| ligne ou checksum non reconstructible | Corrupted |
| dépendance PostgreSQL indisponible | DependencyUnavailable |

La sélection est ordonnée par `effectiveAt DESC`, `recordedAt DESC`, puis `revision DESC`. L'index courant est dérivé du journal et n'est jamais la source d'une lecture historique. Les dates sont reconstruites en UTC canonique.
