# APPART.TEST Local Public Data Restoration 01

## Objet

Restaurer une démonstration publique locale exclusivement par le pipeline déjà certifié, sans SQL manuel, Seeder, modification fonctionnelle ou donnée injectée dans la projection.

## Cause de la disparition

La base locale `appart_test` sert également aux campagnes PostgreSQL. Leur environnement commun appelle `PostgreSqlTestEnvironment::reset()`, qui tronque notamment les décisions Search/SEO/Geography/Media, les générations et projections publiques, puis les données Listing, Property et Media. Le `tearDown()` de la campagne Public Search appelle à nouveau ce reset. Les données P02/P03 étaient donc des données locales réversibles, non une baseline permanente.

## Commande certifiée retenue

`php artisan appart:local:public-fact-listing`

Cette commande refuse tout environnement autre que `local` et toute base autre que `appart_test`. Elle rejoue P02 par `appart:local:first-listing`, puis matérialise une annonce `sale` par Authoring, Listing Lifecycle, Search/SEO sources et Public Projection certifiés.

## Résultat

- transaction : `sale` ;
- ville : `Dakar` ;
- type : `apartment` ;
- canonicalPath : `annonces/p03-appartement-a-vendre-dakar`.
