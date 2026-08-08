# Temporal Read Matrix

| Situation | Résultat |
|---|---|
| aucune révision visible | Missing |
| révision effective et enregistrée avant l'observation | dernière révision visible |
| révision effective future | ignorée |
| révision enregistrée future | ignorée |
| checksum invalide | Corrupted |
| PostgreSQL indisponible | DependencyUnavailable |

Les trois streams sont lus indépendamment et les instants sont canoniques UTC à la microseconde.
