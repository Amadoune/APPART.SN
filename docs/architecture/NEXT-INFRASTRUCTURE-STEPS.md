# Next Infrastructure Steps — après Sprint 2.8

## État certifié

1. Product reste définitivement un catalogue administré immuable.
2. AdministrativeAction est la première tranche PostgreSQL certifiée et la référence méthodologique officielle.
3. Listing est la deuxième tranche PostgreSQL certifiée ; ses 14 contrats partagés passent sans duplication contre Fake et PostgreSQL.
4. Il existe exactement quatre Repositories PostgreSQL concrets : `PostgreSqlAdministrativeActionRepository`, `PostgreSqlListingRepository`, `PostgreSqlPropertyRepository` et `PostgreSqlMediaCollectionRepository`.
5. La suite PostgreSQL combinée passe 91 tests et 415 assertions sur PostgreSQL réel.
6. La suite complète passe 709 tests et 16 745 assertions.
7. Architecture passe 30 tests et 14 856 assertions ; Pint, Larastan et `composer quality` sont verts.

## Chaîne PostgreSQL officielle

`composer test:postgresql` → `phpunit.postgresql.xml` → `APPART_TEST_PG_*` → `PostgreSqlTestEnvironment` → `001_administrative_action.sql` → `002_listing.sql` → `003_property.sql` → `004_media.sql` → contrats, intégration, rollback et concurrence des quatre tranches.

PHP 8.5.8, `pdo_pgsql`, `pgsql` et PostgreSQL 18.x sont obligatoires. L’absence d’un prérequis échoue explicitement ; aucun fallback SQLite n’existe. Les secrets restent dans l’environnement local et ne sont jamais documentés.

## Fondation contractuelle suivante acquise

Au Sprint 2.5, le troisième Registry sélectionné était `RealEstateCatalog / Property`. Son contrat partagé et son harness Fake ont fixé deux réservations permanentes (`PropertyId`, `PropertyReference`), `AddressId` local, Archived terminal et optimistic locking avant l’implémentation 2.6.

## Tranche Property réalisée

Property est la troisième tranche PostgreSQL, avec mapping, réservations, rollbacks, optimistic locking et trois concurrences réelles. La chaîne officielle applique désormais `001_administrative_action.sql`, `002_listing.sql`, puis `003_property.sql`.

## Tranche Media réalisée

`Media / MediaCollection` est la quatrième tranche PostgreSQL. Son contrat partagé fixe la réservation permanente de MediaCollectionId et MediaId, la sauvegarde spécialisée atomique, les items terminaux et le détachement. La migration `004_media.sql` ajoute le schéma propriétaire `media`.

Le Sprint 2.8 est certifié sur PostgreSQL réel avec 91 tests et 415 assertions. Les trois concurrences Media sont couvertes. `is_primary` est transporté explicitement en `1` ou `0`, sans modification du Domain ni affaiblissement des contraintes.

## Prochaine porte

La prochaine étape éventuellement autorisable est la fondation contractuelle dédiée d’un cinquième Registry, après sélection et GO explicites. Aucun cinquième Repository n’est autorisé.

Cette porte n’autorise ni ProductRegistry/ProductRepository, ni Repository générique, ni Persistence partagée, ni Unit of Work globale, ni Outbox complète, ni Dispatcher.
