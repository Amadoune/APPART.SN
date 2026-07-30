# Delivery Runtime Composition — spécification

## Configuration

| Clé | Valeur par défaut | Contrainte portée par le composant |
|---|---:|---|
| `consumer_id` | `public-projection` | identité non vide |
| `worker_id` | `worker:public-projection` | identité non vide |
| `batch_size` | `100` | entier positif |
| `lease_seconds` | `60` | entier positif |
| `maximum_attempts` | `3` | entier positif |
| `retry_delay_seconds` | `1` | délai déterministe |

Les valeurs peuvent être fournies par les variables `PUBLIC_PROJECTION_*` correspondantes. Les Value Objects et composants certifiés restent propriétaires de leur validation.

## Registre

Le Consumer certifié est enregistré explicitement pour la version 1 de :

- `listing.reconstruction.requested` ;
- `property.reconstruction.requested` ;
- `media.reconstruction.requested` ;
- `search.reconstruction.requested` ;
- `content_seo.reconstruction.requested`.

Toute duplication reste refusée par `PublicProjectionDeliveryConsumerRegistry`.
