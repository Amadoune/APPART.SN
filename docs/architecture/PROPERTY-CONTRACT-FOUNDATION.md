# Property Registry Contract Foundation

## Statut

Fondation contractuelle du Sprint 2.5, exécutée uniquement contre `FakePropertyRegistry`. Elle prépare une éventuelle tranche PostgreSQL `RealEstateCatalog / Property` sans créer Repository, mapper, snapshot, transaction, SQL ou migration.

## Inventaire du domaine

- Aggregate Root : `Property`.
- Entité interne courante : `Address`.
- Identités : `PropertyId`; `AddressId` local au Root.
- Business key globale : `PropertyReference`.
- Value Objects persistables : `PropertyType`, `SurfaceArea`, `RoomCount`, `BathroomCount`, `ConstructionYear`, `PropertyStatus`, ainsi que `GeographicPlaceId` et `AddressLine` dans Address. `BusinessYear` est une donnée de validation, pas un état du Root.
- États : `Active`, `Archived`.
- État terminal : `Archived`; update, changement d’adresse et nouvel archivage y sont interdits.
- Version : entier non négatif, initialement zéro, incrémenté exclusivement par le Domain après chaque mutation réussie.
- Reconstruction officielle : `Property::reconstitute`.
- Événements : `PropertyRegistered`, `PropertyUpdated`, `SurfaceChanged`, `AddressChanged`, `PropertyArchived`, avec métadonnées de version et ordre.
- Use cases : `RegisterProperty`, `UpdateProperty`, `ChangeAddress`, `ArchiveProperty`.

## Invariants métier

`PropertyTypePolicy` contrôle les combinaisons type/surface/pièces/salles d’eau/année/adresse. L’année de construction ne dépasse pas l’année métier. Les propriétés résidentielles et bureaux exigent surface, adresse et pièces. Land interdit pièces, salles d’eau et année. Une mutation inchangée, une adresse physique identique, une date antérieure ou toute mutation d’un Root archivé est refusée.

Ces règles restent entièrement dans Domain. Le Registry ne les réévalue pas et ne les déplace pas.

## Profil contractuel

| Axe | Garantie |
|---|---|
| Lecture | `find(PropertyId)` retourne `null` ou un Root détaché fidèle. |
| Add | Réserve atomiquement `PropertyId` et `PropertyReference`; aucun état partiel. |
| Save | Persiste le candidat uniquement si la version durable égale `expectedVersion`. |
| Concurrence | Root absent et version périmée donnent `ConcurrentPropertyModification`. |
| Version | Le Registry n’incrémente et ne réécrit jamais la version. |
| Événements | L’appelant les conserve; une reconstruction ne contient aucun événement historique. |
| Rollback | Un échec de save ne rend aucune mutation visible et conserve les événements appelants. |
| Réservations | PropertyId et PropertyReference sont permanents, y compris après archivage. |
| AddressId | Identité locale conservée avec le snapshot du Root; aucune réservation globale inter-Property. |
| Reconstruction | Toutes les propriétés observables, l’adresse courante, l’état et la version sont fidèles. |
| Terminal | Archived reste terminal après rechargement. |
| Conflits | `PropertyIdConflict`, `PropertyReferenceConflict`, `ConcurrentPropertyModification`. |

## Scénarios partagés

La suite abstraite couvre absence, fidélité add/find, détachement, indépendance des lectures, invisibilité sans save, événements résiduels et rejeu, conservation des événements, conflits distincts d’identité/référence, permanence des réservations après archivage, save avec version exacte, version périmée, Root absent, version portée par le Domain, rollback déterministe, fidélité d’Address et terminalité d’Archived. Chaque scénario crée un Registry frais et utilise des dates et identités fixes.

La classe d’entrée Fake ne redéfinit aucun scénario. Un futur harness PostgreSQL remplacera uniquement la création du Registry, l’isolation physique, les connexions indépendantes et l’échec contrôlé avant commit.

## Règles non applicables

- Aucun historique append-only n’est exposé par Property : les événements en attente ne constituent pas un historique persistant.
- Aucun enfant à identité globalement réservée.
- Aucune réutilisation de PropertyId ou PropertyReference.
- Aucun comportement idempotent métier.
- Aucun `delete` ni désarchivage.
- Le rollback injecté de `add` n’est pas disponible dans le Fake; l’atomicité est vérifiée par les deux conflits, sans écrasement du Root existant.

## Décision

La fondation est réutilisable telle quelle par un futur backend PostgreSQL. Cette décision n’autorise encore aucun artefact de persistance Property et n’accorde aucun GO à un autre Registry.
