# Listing Publication Command Gateway Implementation 01

## Implémentation

`ListingPublicationCommandGatewayV1` est matérialisée par `DeterministicListingPublicationCommandGateway` avec deux opérations :

- `beginReview(listingId, commandId, expectedVersion, actor, occurredAt)` ;
- `approveAndPublish(listingId, commandId, expectedVersion, actor, occurredAt)`.

La Gateway relit le workflow et l'Aggregate, vérifie leur paire d'états, réserve la commande, résout les autorités internes puis compose les use cases existants.

BeginReview résout la révision et l'evidence, applique le workflow, puis `SendToReview`. ApproveAndPublish résout en plus l'expiration et la MediaCollection owner-scoped depuis le PropertyId de l'Aggregate, puis appelle `PublishListing` avec les validations existantes.

Une transaction locale englobe ledger, workflow, Aggregate, Registry, Public Facts et append événementiel. Une transaction externe est préservée par savepoint.

Résultats fermés : `Applied`, `AlreadyApplied`, `VersionConflict`, `StateConflict`, `Missing`, `AuthorityUnavailable`, `TransitionDenied`, `DivergentCommand`, `DependencyUnavailable`.

Aucun accès Search ou Projection, aucune UI et aucune modification PublicationReview, IAM, Property ou Media. P08 reste fermé.
