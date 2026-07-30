# Stratégie PostgreSQL Property-to-Listings

La migration `012_property_listings_resolution.sql` ajoute uniquement l'index composite
`listing_lifecycle.listings(property_id, id)`. Il supporte simultanément le prédicat d'ownership et
l'ordre keyset. La migration est idempotente avec `CREATE INDEX IF NOT EXISTS`.

La lecture est sans verrou et sans mutation. Elle ne charge jamais plus de `limit + 1` identités.
Le rollback de déploiement consiste à exécuter :

```sql
DROP INDEX IF EXISTS listing_lifecycle.listings_property_id_id_idx;
```

La suppression de l'index ne modifie aucune donnée et restaure seulement le plan d'accès antérieur.
