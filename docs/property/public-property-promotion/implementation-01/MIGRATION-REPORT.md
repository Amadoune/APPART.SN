# Rapport de migration

## Audit

Les migrations existantes se terminaient à 099 ; 100 était le prochain numéro libre au moment de l’implémentation.

## Migration 100

`100_property_promotion.sql` ajoute uniquement `real_estate_catalog.property_promotion_commands` :

- clé primaire `command_id` ;
- checksum SHA-256 contraint ;
- `property_id` unique et référencé vers le registre Property ;
- owner, version Authoring, instant et résultat réussi.

Aucun backfill et aucune modification de 098/099. Le rollback dédié supprime exclusivement cette table. Le test PostgreSQL de migration démontre apply, contraintes et rollback.
