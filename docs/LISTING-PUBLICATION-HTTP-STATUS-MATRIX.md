# Listing Publication HTTP Status Matrix

| Statut applicatif | HTTP | Sens |
|---|---:|---|
| `Applied` | 200 | transition et événement atomiquement persistés |
| `AlreadyApplied` | 200 | rejeu idempotent déjà matérialisé |
| `Denied` | 422 | commande formée mais refusée par le workflow |
| `ConcurrencyConflict` | 409 | version ou état concurrent incompatible |
| `PersistenceFailure` | 503 | capacité durable temporairement indisponible |
| requête invalide | 422 | erreur de présence, format ou type |
| UUID de route invalide | 404 | ressource de commande non adressable |

Aucun autre statut applicatif n'existe et aucune branche implicite n'est admise.
