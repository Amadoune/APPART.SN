# Stratégie PostgreSQL des décisions Search

La table `search_discovery.public_search_decisions` contient une ligne courante par Listing :

- `listing_id`, clé primaire de lecture ;
- `decision_id`, identité unique de décision ;
- `version`, entier strictement positif ;
- `state`, contrainte aux états Search certifiés ;
- `payload`, JSONB de la décision finale ;
- `payload_checksum`, SHA-256 canonique ;
- `updated_at`, métadonnée opérationnelle qui ne participe jamais à la version.

La migration est idempotente. Le rollback structurel consiste à supprimer uniquement `search_discovery.public_search_decisions`. La table ne possède aucune dépendance sortante et sa suppression ne modifie aucun Aggregate ni fait source.

La concurrence est contrôlée par verrou de la ligne Listing et par un upsert autorisé uniquement lorsque la version entrante est supérieure. Les écritures concurrentes identiques convergent sans double effet.
