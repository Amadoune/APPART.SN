# Frontière HTTP Authoring

| Surface | Authentification | Rôle/capability | Ownership | Source de décision |
|---|---|---|---|---|
| `GET /authoring/workspace` | session valide | aucun | non applicable à la vue | `RequireIdentityAccessSession` |
| Geography selections | session valide | aucun | aucune donnée owner mutée | middleware + Request |
| Property create/read/update | session valide | aucun | AccountId session créé/comparé à ownerAccountId | Controller + Runtime |
| Listing create/read/update/preview | session valide | aucun | owner/delegation `VIEW` ou `EDIT` | Runtime Authoring |
| Media upload/read/archive | session valide | aucun | AccountId session comparé au Property owner | Media Controller + Runtime |
| Public Authoring journey | session valide | aucun | AccountId session devient actor | Controller + Operations |
| Submit | session valide | aucun | owner/delegation `SUBMIT` | Operations |

Les Form Requests autorisent uniquement si l’attribut `iam_account_id` existe. Aucun owner fourni par payload n’est accepté comme autorité.
