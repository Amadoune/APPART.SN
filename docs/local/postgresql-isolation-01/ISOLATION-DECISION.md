# Isolation Decision

La convention finale retenue est :

- application locale `appart.test` : `appart_rebuild` ;
- suites PostgreSQL automatisées : `appart_test`.

Cette décision reprend les noms déjà présents dans `.env.example` et `.env.postgresql.example`. `APPART_APPLICATION_PG_DATABASE=appart_rebuild` devient l'identité explicite opposée à la base de test.

Les commandes locales de démonstration sont alignées sur `appart_rebuild`. Aucun contrat Domain/Application n'est modifié.
