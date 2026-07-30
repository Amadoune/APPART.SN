# MediaCollection PostgreSQL Vertical Slice

## Périmètre

La tranche persiste exclusivement `Media / MediaCollection`. Elle comprend `MediaCollectionSnapshot`, `MediaItemSnapshot`, `MediaCollectionMapper`, une transaction locale, `PostgreSqlMediaCollectionRepository` et la migration `004_media.sql`. Les 14 scénarios du contrat partagé sont exécutés sans duplication contre Fake et PostgreSQL.

## Mapping

La racine conserve `MediaCollectionId`, `PropertyId`, `version` et `lastChangedAt` avec microsecondes et offset. Chaque item conserve `MediaId`, propriétaire, type, checksum, ordre, caption nullable, source, statut, primaire et ses trois dates possibles. Le mapper emploie uniquement l’API publique et `MediaCollection::reconstitute()`; une reconstruction ne produit aucun événement.

## Modèle physique

Le schéma propriétaire `media` contient `media_collections`, `media_items` et `media_id_reservations`. Les réservations `MediaId` sont permanentes et distinctes de l’état courant. Les contraintes imposent version non négative, checksum local unique, ordre actif unique, primaire actif unique, états et dates terminales cohérents, ainsi que des FK `RESTRICT`. Aucune suppression physique d’une réservation n’est exposée.

## Transactions et conflits

`add()` réserve atomiquement la racine. `save()` applique le verrou optimiste sur `version`. `saveWithMediaReservation()` réserve définitivement le `MediaId` et écrit la mutation dans une seule transaction. Tout échec annule racine, items et réservation sans consommer les événements appelants. Les erreurs publiques sont `MediaCollectionIdConflict`, `MediaIdConflict`, `ConcurrentMediaCollectionModification` et `PersistentMediaCollectionIntegrity`; aucun détail SQL ne fuite.

## Concurrence

Trois courses à deux processus et deux connexions prouvent un gagnant unique pour un même `MediaCollectionId`, un même `MediaId` et un même `expectedVersion`, sans mutation partielle.

## Certification

La preuve opérateur sur PostgreSQL réel est verte : 91 tests, 415 assertions, zéro erreur, zéro échec, en 17,275 secondes. Elle couvre les 14 contrats PostgreSQL Media, l’intégration, les trois courses réelles et les trois tranches antérieures.

Le bind PDO de `is_primary` sérialise explicitement le booléen en `1` ou `0`. La cause technique reste chaînée dans `PersistentMediaCollectionIntegrity`, tandis que son message public demeure stable. Aucune contrainte SQL, règle métier, réservation permanente ou garantie d’optimistic locking n’a été modifiée.

Les validations hors PostgreSQL sont également vertes : mapper 8 tests et 12 assertions, suite complète 709 tests et 16 745 assertions, Architecture 30 tests et 14 856 assertions, Pint, Larastan zéro erreur, `composer quality`, `git diff --check`, audit secrets zéro occurrence et exactement quatre Repositories PostgreSQL.
