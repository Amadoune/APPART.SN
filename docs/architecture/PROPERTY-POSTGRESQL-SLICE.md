# Property PostgreSQL Vertical Slice

## Statut

Troisième tranche PostgreSQL concrète, limitée à `RealEstateCatalog / Property`. Elle réutilise sans duplication les 14 scénarios de `PropertyRegistryContract`.

## Aggregate et mapping

Le Root persiste `PropertyId`, `PropertyReference`, type, surface optionnelle, nombres de pièces et salles d’eau, année de construction optionnelle, Address optionnelle, statut, `lastChangedAt` et version. Address conserve `AddressId`, `GeographicPlaceId` et `AddressLine`.

`PropertySnapshot` et `AddressSnapshot` sont explicites et `final readonly`. `PropertyMapper` utilise l’API publique, dont l’accesseur en lecture seule `lastChangedAt()`, puis `Property::reconstitute`. Il invoque `PropertyTypePolicy` pour les combinaisons structurelles sans recopier ses règles. L’aller-retour conserve microsecondes et offset et produit un Root sans événement.

## Modèle physique

- Schéma propriétaire : `real_estate_catalog`.
- `properties` : Root, clé primaire PropertyId, données descriptives, état, date/offset et version.
- `property_reference_reservations` : réservation permanente de PropertyReference, rattachée au Root par FK restrictive.
- `property_addresses` : Address courante, ownership par Property et identité locale.
- Index : `property_addresses_place_idx`.
- Migration : `003_property.sql`, après `001_administrative_action.sql` et `002_listing.sql`.

La table de réservation dédiée est nécessaire pour traduire sans ambiguïté et sans analyser un message localisé les courses distinctes PropertyId/PropertyReference. Aucun delete de Root ou de réservation n’existe.

## Contraintes et transactions

Les contraintes couvrent identités, ownership, types/statuts connus, bornes structurelles, version non négative, clés étrangères `ON DELETE RESTRICT` et AddressId local. Elles ne réimplémentent pas `PropertyTypePolicy`.

`PropertyTransaction` et `PostgreSqlPropertyTransaction` forment une frontière locale injectable. Add écrit Root, référence et Address atomiquement. Save conditionne la mise à jour à `expectedVersion` et écrit Address dans la même transaction. Les échecs avant commit annulent toutes les lignes.

## Erreurs, concurrence et événements

- conflit de Root lors de l’étape Root → `PropertyIdConflict` ;
- conflit lors de la réservation dédiée → `PropertyReferenceConflict` ;
- Root absent ou version périmée → `ConcurrentPropertyModification` ;
- snapshot ou écriture incohérents → `PersistentPropertyIntegrity`.

Trois courses utilisent deux processus et deux connexions : même PropertyId, même PropertyReference et même `expectedVersion`. Chacune produit un gagnant unique, sans Root, réservation ou Address partiel.

Le Repository ne consomme ni ne persiste les événements. Les événements appelants survivent aux succès et échecs; les Roots reconstruits n’en contiennent aucun.

## Limites

Archived reste terminal selon Domain. Il n’existe ni désarchivage, delete, historique append-only, idempotence, Outbox, Dispatcher, Unit of Work globale, projection ou composant web.
