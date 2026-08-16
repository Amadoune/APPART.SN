# F4 — Migration Report

## Identité

- up : `099_property_authoring_source_completeness.sql` ;
- rollback : `099_property_authoring_source_completeness.down.sql` ;
- owner : `real_estate_catalog_authoring.property_authoring`.

## Évolution additive

Les colonnes `property_reference`, `surface_square_meters`, `rooms`, `bathrooms`, `construction_year`, `geographic_place_id`, `address_line` et `address_intent_id` sont ajoutées nullable. Les contraintes SQL reprennent seulement les bornes primitives existantes ; aucune règle `PropertyTypePolicy` n’est dupliquée.

Il n’existe aucun backfill, défaut artificiel, donnée P02, PlaceId dérivé, AddressIntentId historique, BusinessYear ou AddressId.

## Compatibilité et rollback

Les lignes pré-099 restent inchangées et lisibles avec huit valeurs nulles. Le rollback dédié retire contraintes et colonnes F4 uniquement. La campagne PostgreSQL démontre : up, lecture legacy, écriture enrichie, replay, optimistic locking, down transactionnel et réapplication idempotente.
