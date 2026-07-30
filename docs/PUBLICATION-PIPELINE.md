# Publication Pipeline — Sprint 3.1

## Workflow

`PublishListing` reçoit l'identité du Listing, l'identité de la MediaCollection, une nouvelle révision, une expiration et la preuve de transition.

1. Charger le Listing détaché et mémoriser sa version.
2. Lire l'éligibilité actuelle du Property référencé par le Listing.
3. Lire l'éligibilité de publication de la MediaCollection pour ce Property.
4. Demander au Listing de publier. L'Aggregate et sa policy valident toutes les règles.
5. Sauvegarder le Listing avec la version attendue.

Le cas d'usage ne contient aucune règle métier : il charge, compose des faits, délègue la décision et sauvegarde.

## Préconditions

- Listing existant;
- Property existant et `Eligible` (donc notamment non archivé);
- MediaCollection existante, appartenant au Property du Listing;
- au moins un média actif;
- média principal actif présent;
- transition vers `Published` autorisée depuis l'état courant;
- trigger et origin conformes au graphe (`FavorableReview`, `RegularizationValidated` ou renouvellement direct selon l'état);
- révision nouvelle, preuve datée non rétroactive et expiration future.

## Postconditions

- statut du Listing : `Published`;
- nouvelle révision append-only;
- expiration renseignée;
- version du Listing incrémentée;
- exactement un `ListingPublished` produit par le Listing;
- Property et MediaCollection inchangés;
- aucun événement n'est diffusé par ce sprint.

## Échecs

| Cause | Erreur publique du Domain | Écriture visible |
|---|---|---|
| Listing absent | `ListingNotFound` | aucune |
| Property absent/archivé/inéligible/indisponible | `TransitionConditionNotSatisfied` | aucune |
| MediaCollection absente | `TransitionConditionNotSatisfied` (`missing`) | aucune |
| Collection d'un autre Property | `TransitionConditionNotSatisfied` (`property_mismatch`) | aucune |
| Aucun média actif | `TransitionConditionNotSatisfied` (`without_active_media`) | aucune |
| Aucun principal actif | `TransitionConditionNotSatisfied` (`without_primary_media`) | aucune |
| État/transition Listing incompatible | `ListingViolation` ou `TransitionConditionNotSatisfied` | aucune |
| Expiration, révision ou date invalide | exception Listing existante | aucune |
| Concurrence à la sauvegarde | `ConcurrentListingModification` | aucune |

## Frontières

Le pipeline n'utilise aucun nouveau Repository, Mapper, Snapshot, SQL, migration, projection, API, Outbox, Dispatcher ou Unit of Work. `MediaCatalog` est un port de lecture applicatif de ListingLifecycle, analogue au `PropertyCatalog` existant; son résultat est un fait normalisé et non une copie de l'Aggregate Media.
