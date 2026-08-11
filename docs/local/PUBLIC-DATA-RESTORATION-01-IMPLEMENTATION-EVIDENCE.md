# Public Data Restoration 01 — Implementation Evidence

## Exécution

La commande existante `appart:local:public-fact-listing` a terminé avec exit code 0. Aucun nouveau code, aucune commande, migration ou surface technique n'a été créé.

Chaîne utilisée :

`appart:local:first-listing` → Authoring → Listing Lifecycle → faits publics scellés → sources Search/SEO → projection publique → activation → Reader public.

## Garanties

- aucune instruction SQL manuelle ;
- aucune écriture directe dans une table de projection ;
- aucun Seeder ;
- aucun mock ou résultat synthétique ;
- replay déterministe avec identifiants locaux fixes ;
- garde-fous `APP_ENV=local` et `DB_DATABASE=appart_test` ;
- aucune modification de P09, Search, Projection, Listing Lifecycle, Property, Media, IAM ou SEO.
