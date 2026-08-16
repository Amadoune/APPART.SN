# Preuves migration 100

La migration additive crée exclusivement `real_estate_catalog.property_promotion_commands` avec :

- `command_id` UUID, clé primaire ;
- checksum hexadécimal SHA-256 contraint ;
- `property_id` UUID unique et FK restrictive vers Property ;
- owner UUID ;
- version Authoring positive ;
- instant avec microsecondes ;
- résultat fermé `applied`.

Le test PostgreSQL F6 couvre up, contraintes et down. Le harness applique réellement 100 et nettoie la table. Aucun backfill et aucune altération de 098/099. Aucune migration 101 n’est créée.
