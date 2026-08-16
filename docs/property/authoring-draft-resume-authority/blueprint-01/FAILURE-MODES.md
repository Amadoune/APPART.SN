# Failure Modes

| Situation | Résultat |
|---|---|
| session absente/expirée | HTTP 401 avant Reader |
| listing absent ou owner mismatch | NotFoundOrForbidden / 404 |
| Property ou Draft absent | Incomplete ou StateConflict / 409 |
| Portfolio absent pour le Listing | NotFoundOrForbidden / 404 |
| versions divergentes | StateConflict / 409 |
| Aggregate/Workflow non draft ou incohérents | StateConflict / 409 |
| GeographicPlaceId absent | Incomplete / 409 |
| Place corrompu | Corrupted / 500 |
| ancien proof context absent | non bloquant en lecture ; nouvelle preuve exigée à l’édition |
| Media vide | Available, step Photos |
| Media store indisponible | DependencyUnavailable / 503 |
| payload persistant invalide | Corrupted / 500 |

Tous les résultats sont fail-closed. Aucun fallback ne sélectionne un autre draft et aucune réparation n’est effectuée.
