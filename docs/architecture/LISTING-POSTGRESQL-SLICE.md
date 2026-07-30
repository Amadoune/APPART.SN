# Listing PostgreSQL Vertical Slice

## Statut

Deuxième tranche PostgreSQL concrète du projet, strictement limitée à `ListingLifecycle / Listing`. Elle suit AdministrativeAction comme référence méthodologique et réutilise sans duplication les 14 scénarios de `ListingRegistryContract`.

## Aggregate persisté

Le Root conserve `ListingId`, `PropertyId`, `ListingStatus`, dernière date de changement dérivée de la dernière révision, `ExpirationDate` optionnelle, version entière et historique ordonné de `ListingRevision`.

Chaque révision conserve `ListingRevisionId`, position locale, état précédent optionnel, état résultant, `ActorId`, `TransitionTrigger`, `TransitionReason`, `TransitionOrigin` et `occurredAt`. `ListingId` est réservé définitivement. `ListingRevisionId` est unique uniquement dans son Listing. Il n’existe aucune business key.

Les dix états persistables sont Draft, Submitted, UnderReview, ChangesRequested, Published, Suspended, Expired, Withdrawn, Rejected et Archived. Archived est terminal dans le Domain ; Expired et Withdrawn restent réactivables. Infrastructure n’ajoute aucune transition.

## Snapshots et mapping

`ListingSnapshot` et `ListingRevisionSnapshot` sont `final readonly`, complets et sans PDO/SQL. `ListingMapper` utilise exclusivement l’API publique et `Listing::reconstitute`. Il valide identités, enums, dates, version, séquence contiguë, unicité locale, chaîne des états, ordre temporel et cohérence de l’état courant.

Le mapper ne consomme jamais les événements du candidat. Un Aggregate reconstruit ne contient aucun événement. L’offset temporel observable est conservé explicitement en plus de l’instant `timestamptz(6)`.

## Modèle PostgreSQL

- Schéma propriétaire : `listing_lifecycle`.
- `listings` : clé primaire `id`, propriété, état contrôlé, dates et offsets, version non négative.
- `listing_revisions` : clé primaire `(listing_id, sequence)`, unicité `(listing_id, revision_id)`, FK `ON DELETE RESTRICT`, checks de séquence, état et origine.
- Index : `listing_revisions_listing_sequence_idx`.
- Migration : `002_listing.sql`, exécutée après `001_administrative_action.sql`.

Aucune suppression physique n’existe. La clé primaire matérialise la réservation permanente de `ListingId`.

## Repository, transaction et erreurs

`PostgreSqlListingRepository` implémente directement `ListingRegistry`. `find` restitue un Root détaché. `add` écrit Root et historique initial atomiquement. `save` compare d’abord le préfixe append-only puis met à jour le Root seulement si la version durable égale `expectedVersion`; il ajoute uniquement le suffixe des révisions.

`ListingTransaction` et `PostgreSqlListingTransaction` constituent une frontière locale injectable. Les échecs contrôlés avant commit prouvent le rollback complet de `add` et `save`.

La clé dupliquée à l’ajout devient `ListingIdConflict`. Root absent ou version périmée deviennent `ConcurrentListingModification`. Snapshot, historique ou écriture incohérents deviennent `PersistentListingIntegrity`, sans exposition de SQL, table, connexion ou secret.

## Concurrence et tests

Deux processus PHP et deux connexions indépendantes prouvent un gagnant unique pour `add` concurrent et pour `save` concurrent au même `expectedVersion`. Le perdant ne laisse aucune révision partielle.

La tranche comprend tests mapper, 14 contrats partagés PostgreSQL, intégration des contraintes et rollbacks, et deux courses réelles. La suite PostgreSQL commune continue d’exécuter AdministrativeAction et Listing sur PostgreSQL 18.x réel, sans fallback SQLite.

## Limites

Aucun événement n’est persisté ou publié. Outbox, Dispatcher et Unit of Work globale restent différés. Aucun autre Registry, projection, composant web ou abstraction générique n’est introduit.
