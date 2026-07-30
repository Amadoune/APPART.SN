# Architecture Baseline — Sprint 2.2

## Mise à jour Sprint 2.8

`Media / MediaCollection` devient la quatrième tranche PostgreSQL concrète. Elle possède deux snapshots typés, un mapper exhaustif, une transaction locale, `PostgreSqlMediaCollectionRepository`, le schéma propriétaire `media` et la migration `004_media.sql`. Les tables sont `media_collections`, `media_items` et `media_id_reservations`.

Les 14 contrats Media sont partagés sans duplication entre Fake et PostgreSQL. La réservation permanente de `MediaId`, le rollback total, l’optimistic locking et les trois courses réelles sont obligatoires. Il existe exactement quatre Repositories PostgreSQL ; aucun cinquième Registry, Repository générique, Unit of Work globale, Outbox ou Dispatcher n’est autorisé.

Le Sprint 2.8 est certifié sur PostgreSQL réel : la suite combinée passe 91 tests et 415 assertions, sans erreur ni échec. Les trois courses Media sont couvertes. Le bind de `is_primary` utilise explicitement `1` ou `0` pour la colonne PostgreSQL `boolean`; cette correction de transport n’affaiblit aucune contrainte, réservation, règle métier ou garantie d’optimistic locking. La certification globale associée est 709 tests et 16 745 assertions, Architecture 30 tests et 14 856 assertions, Pint PASS, Larastan zéro erreur et `composer quality` PASS.

## État de référence

Au 17 juillet 2026, APPART.SN est un monolithe modulaire PHP 8.5 / Laravel 13 dont le cœur métier est indépendant du framework. Treize enveloppes existent sous `src/Modules`; douze contiennent Domain et Application, `LegacyMigration` reste vide. L’état comprend 14 Aggregate Roots, 52 modèles de domaine, 156 Value Objects, 108 fichiers d’événements, 85 fichiers de Use Cases/support, 29 ports/contracts et 28 fakes.

PostgreSQL 18.x est la cible normative. Quatre persistances sont présentes : AdministrativeAction, Listing, Property et MediaCollection, chacune avec son Repository concret, son mapper, ses snapshots, sa transaction locale, sa migration et son schéma propriétaire. Aucun cinquième module ne possède Repository concret, mapper, migration, SQL ou adapter ; aucun modèle Eloquent, Unit of Work globale, Outbox complète, Dispatcher, Controller métier ou Service Provider métier n’existe.

## Organisation observée

- `src/Modules/<Module>/Domain` : modèles, Value Objects, événements, exceptions et politiques.
- `src/Modules/<Module>/Application` : contrats locaux et orchestration des cas d’usage.
- `tests/Unit/Modules/<Module>` : tests métier et fakes en mémoire.
- `tests/Architecture` : garde-fous physiques et de dépendances.
- `docs/infrastructure` : huit blueprints normatifs, inchangés pendant ce sprint.

## Autorités normatives protégées

`REPOSITORY-PROTOCOL.md`, `UNIT-OF-WORK.md`, `TRANSACTION-POLICY.md`, `OUTBOX-BLUEPRINT.md`, `EVENT-DISPATCHER-BLUEPRINT.md`, `PERSISTENCE-MAPPING-STRATEGY.md`, `POSTGRESQL-STRATEGY.md` et `REPOSITORY-TESTING-STRATEGY.md` définissent la future infrastructure. Aucun de ces fichiers n’est modifié par la baseline.

## Garanties acquises

- Domain sans Laravel, Infrastructure ou dépendance inter-module.
- Ports définis depuis le module consommateur ; échanges par identités et snapshots locaux.
- Aggregate Roots versionnés, détachables et reconstructibles.
- Registries mutables utilisant une version attendue.
- Projections Search et SEO explicitement reconstruisibles.
- Tests métier sans base ni réseau ; garde-fous statiques déterministes.

## Limites

- aucune preuve de mapping ou de comportement PostgreSQL ;
- aucune preuve transactionnelle, de concurrence réelle, Outbox ou Dispatcher ;
- `LegacyMigration` non implémenté ;
- persistance de `Product` non contractualisée ;
- réservations uniques à consolider par port avant schéma.

## Mise à jour Sprint 2.3A

Les deux conditions du Sprint 2.2 sont levées : ADR-1006 fixe définitivement Product comme catalogue administré immuable et `RESERVATION-STRATEGY.md` fixe les réservations des quatorze Roots. `CONTRACT-TEST-BLUEPRINT.md` est normatif.

Le verdict devient **GO limité aux tests contractuels Fake**. La suite pilote `AdministrativeActionRegistryContract` doit prouver le Fake avant toute implémentation. Aucun GO Repository PostgreSQL n’est accordé par cette baseline ; il dépend du bilan complet du Sprint 2.3A.

## Mise à jour Sprint 2.3B

Le Sprint 2.3A est validé : 19 scénarios partagés verts contre le Fake et qualité globale verte. Un GO encadré a permis la seule tranche `AdministrationAudit / AdministrativeAction`.

La tranche candidate contient snapshot, mapper, migration SQL propriétaire, Repository PDO, transaction locale, harness PostgreSQL et tests mapping/intégration/concurrence. Aucun GO général n’existe pour les douze autres Registries. Product reste un catalogue administré immuable.

La conformité 2.3B est **GO et close** sur PostgreSQL 18.4 réel : 27/27 tests, 94 assertions, dont 19 contrats partagés, 6 intégrations/contraintes/rollback et 2 concurrences réelles. Les deux courses garantissent un gagnant unique et aucune mutation partielle. La certification globale associée est 606/606 tests (12 973 assertions), Architecture 23/23 (11 439 assertions), Pint PASS, Larastan zéro erreur et `composer quality` PASS.

## Mise à jour Sprint 2.3C

`ListingRegistry` dispose d’une fondation contractuelle partagée exécutée exclusivement contre son Fake. Son profil est : réservation permanente de `ListingId`, aucune business key, `ListingRevisionId` local à la racine, historique append-only, verrouillage optimiste et reconstruction sans événements résiduels. `Archived` est terminal ; `Expired` et `Withdrawn` sont réactivables. Aucun artefact PostgreSQL Listing n’est présent. Product reste un catalogue administré immuable. Le GO éventuel suivant est limité à la seule tranche Listing ; Outbox, Dispatcher et Unit of Work globale restent différés.

## Mise à jour Sprint 2.3D

AdministrativeAction est la référence officielle de méthode pour chaque future tranche PostgreSQL. Le standard réutilisable couvre snapshots immuables et spécifiques, mapper exhaustif, reconstruction officielle, transaction locale injectable, rollback total, optimistic locking conditionnel, traduction stable des conflits, contrat partagé et concurrence réelle. Approval, Decision, AuditEntry, four-eyes et le schéma `administration_audit` restent spécifiques au métier témoin.

Les documents `POSTGRESQL-REPOSITORY-GUIDE.md`, `POSTGRESQL-CHECKLIST.md` et `POSTGRESQL-NAMING.md` gouvernent désormais chaque autorisation. Les tests d’architecture interdisent les abstractions Repository/Mapper/Snapshot génériques ou partagées, leur placement dans Domain/Application et SQL/PDO hors Infrastructure. À la clôture 2.3D, AdministrativeAction était encore le seul Repository concret ; le Sprint 2.4 autorise ensuite explicitement Listing.

## Mise à jour Sprint 2.4

Listing devient la deuxième tranche PostgreSQL concrète et autorisée. Elle comprend deux snapshots typés, un mapper, une transaction locale, un Repository PDO, le schéma propriétaire `listing_lifecycle`, les tables `listings` et `listing_revisions`, et la migration `002_listing.sql`. Les 14 scénarios contractuels sont réutilisés sans duplication contre PostgreSQL.

À la clôture du Sprint 2.4, il existait exactement deux Repositories concrets : `PostgreSqlAdministrativeActionRepository` et `PostgreSqlListingRepository`. Le Sprint 2.6 autorise ensuite explicitement Property.

## Mise à jour Sprint 2.5

Au Sprint 2.5, `PropertyRegistry` obtient une fondation contractuelle partagée exécutée contre son Fake, encore sans Infrastructure Property. Son profil réserve définitivement `PropertyId` et la business key `PropertyReference`, conserve `AddressId` local au Root, applique une sauvegarde conditionnelle par version et reconstruit Archived comme état terminal sans événement résiduel.

Les tests d’architecture maintenaient alors exactement deux Repositories PostgreSQL concrets et interdisaient toute persistance Property avant le GO dédié du Sprint 2.6.

## Mise à jour Sprint 2.6

Property devient la troisième tranche PostgreSQL concrète. Elle comprend snapshots Property/Address, mapper, transaction locale, Repository, migration `003_property.sql`, schéma `real_estate_catalog`, réservations permanentes PropertyId/PropertyReference et trois preuves de concurrence réelle.

Il existe exactement trois Repositories concrets : `PostgreSqlAdministrativeActionRepository`, `PostgreSqlListingRepository` et `PostgreSqlPropertyRepository`. Aucun quatrième Repository, abstraction générique, Persistence partagée, Unit of Work globale, Outbox, Dispatcher, Eloquent métier ou artefact web n’existe.

## Mise à jour Sprint 2.7

`MediaCollectionRegistry` possède une fondation contractuelle partagée exécutée contre son Fake, sans Infrastructure Media. Le profil réserve MediaCollectionId à l’ajout et chaque MediaId définitivement via `saveWithMediaReservation`; checksum et ordre restent locaux. La reconstruction conserve date, version, items, primaire et états Removed/Archived terminaux sans événement résiduel.

Les tests d’architecture maintiennent exactement trois Repositories PostgreSQL et interdisent toute persistance Media avant un GO dédié.
