# Contract Proposal

## Surface conceptuelle

Nom candidat : `AuthoringPublicFactHandoffV1`.

Le contrat n'est pas implémenté par ce jalon. Il devra accepter une commande immutable contenant exclusivement :

- `listingId` ;
- `authoringVersion` ;
- `transactionKind` (`sale|rent`) ;
- `sourceIntentId` ;
- `sourceChecksum` ;
- `observedAt` UTC canonique.

## Résultats fermés proposés

- `Applied` ;
- `AlreadyApplied` ;
- `DivergentIntent` ;
- `VersionConflict` ;
- `ListingMismatch` ;
- `DependencyUnavailable`.

## Composition

Le handoff est appelé par l'orchestrateur Authoring dans la même transaction locale que Submit lorsque les deux participants partagent la transaction Listing existante. Il persiste les faits candidats avant de faire avancer le workflow. Aucun commit implicite et aucune transaction distribuée.

`ApproveAndPublish` vérifie et scelle les faits candidats, sans consulter Authoring. La projection consomme ensuite le fait scellé depuis la frontière Listing Lifecycle.

## Limites

Le contrat ne transporte pas titre, description, prix, devise, charges, disponibilité ou préférence de contact. Il ne décide ni publication, ni éligibilité, ni Search, ni SEO.

## Conditions avant implémentation

- décision d'autorité sur le support persistant additif ;
- versionnement des snapshots et événements futurs ;
- stratégie explicite pour les Listings historiques et P02 ;
- tests d'atomicité, idempotence, concurrence et rollback externe.
