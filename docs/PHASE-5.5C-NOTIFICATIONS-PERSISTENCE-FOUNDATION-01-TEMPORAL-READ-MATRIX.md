# Notifications Persistence — Temporal Read Matrix

| Condition | Résultat |
|---|---|
| Aucune révision visible | `Missing` |
| Révision effective et enregistrée avant observation | Dernière révision visible |
| Checksum ou ligne invalide | `Corrupted` |
| Dépendance PostgreSQL indisponible | `DependencyUnavailable` |

Les timestamps sont UTC, canonisés à la microseconde et la lecture provient exclusivement du journal.
