# Stratégie PostgreSQL — Public Media

## Schéma

La migration `011_public_media_decisions.sql` crée `public_media.decisions`. La clé primaire `media_collection_id` borne toutes les lectures et écritures à une collection.

La ligne conserve la version, la causalité, le checksum de révision, le payload JSONB et son checksum technique. Les contraintes imposent une version positive, une causalité non vide, des SHA-256 valides et l'égalité des deux checksums.

## Transactions

Le writer respecte la transaction PDO déjà ouverte. Sinon, il ouvre et termine une transaction locale. Toute exception annule sa transaction locale ; une transaction externe reste sous le contrôle de l'appelant.

## Monotonie et concurrence

Un advisory lock transactionnel dérivé de l'identité sérialise les écritures concurrentes d'une même collection. Après lecture `FOR UPDATE`, le writer refuse les versions anciennes et les divergences de même version. Les collections distinctes ne partagent aucun verrou applicatif.

`updated_at` reste une métadonnée d'exploitation et ne participe jamais à la version publique.

## Rollback

La table ne sépare jamais contenu et révision. Un rollback restaure donc l'état complet précédent, sans révision orpheline ni contenu partiel.
