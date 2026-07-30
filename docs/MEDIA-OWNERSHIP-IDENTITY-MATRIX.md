# Matrice des identités Media Ownership

| Source | Relation officielle | Résultat |
|---|---|---|
| Listing | `Listing::propertyId()` | identité Property explicite |
| Property | valeur UUID du Property | clé de lookup `property_id` |
| MediaCollection | `MediaCollection::propertyId()` | ownership durable |
| PostgreSQL | `media.media_collections.property_id` | zéro, une ou plusieurs collections |

Aucune conversion sémantique n’est effectuée entre ces étapes. Les types propres aux modules partagent la même valeur persistée uniquement à travers le port spécialisé.
