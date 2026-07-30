# Stratégie PostgreSQL — Deterministic Decision Time

## Persistance réutilisée

La table certifiée `content_seo.public_source_snapshots` reste l'unique persistance. `decision_at` est une propriété du JSONB canonique et `payload_checksum` porte le SHA-256 de l'ensemble du snapshot.

Aucune migration, table, colonne ou index supplémentaire n'est nécessaire. Le reader spécialisé ne contient lui-même aucun SQL : il compose l'adaptateur PostgreSQL Content/SEO certifié.

## Atomicité et rollback

Le writer 3.8C écrit le snapshot complet dans une seule ligne et participe aux transactions externes. `decisionAt` ne peut donc pas être observé sans sa décision. Un rollback restaure simultanément la décision et sa valeur temporelle.

## Horloges techniques

`updated_at` et `clock_timestamp()` restent des métadonnées de persistance et ne sont jamais lus par cette fondation. Ils ne peuvent influencer `decisionAt`.
