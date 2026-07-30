# Stratégie PostgreSQL Content/SEO Source Snapshot

`content_seo.public_source_snapshots` stocke une ligne courante par Listing, une identité unique, une version positive, un payload JSONB et son checksum SHA-256. `updated_at` est exclusivement opérationnel et ne participe jamais à la version.

L’upsert ne remplace une ligne que par une version supérieure. Un verrou par Listing et la condition d’upsert garantissent la convergence concurrente.

La migration est idempotente. Le rollback structurel supprime uniquement cette table spécialisée ; aucune donnée Aggregate ou réservation canonical n’est concernée.
