# Media Item Lifecycle Outbox Owner Schema Resolution

| Source module | Schéma PostgreSQL | Résolution inverse |
|---|---|---|
| `Media` | `media` | `Media` |

Le Writer résout le schéma exclusivement depuis `message.sourceModule`. Le Reader parcourt le catalogue fermé des owners, restaure le module depuis le schéma et filtre chaque lecture par `source_module`.

Aucun résolveur, Writer ou Reader spécifique à Media n'est créé.
