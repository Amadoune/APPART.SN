# APPART.TEST LISTING PUBLICATION REGISTRY SYNCHRONIZATION 01 — Boundary Audit

## Verdict de frontière

`NO GO PROPOSÉ`

La synchronisation ne peut pas être ajoutée derrière le contrat actuel sans inventer des données de transition métier ou modifier une surface certifiée.

## Autorités observées

- `ListingPublicationOrchestrator` décide et persiste l'état du workflow de publication.
- `ListingRegistry` persiste l'Aggregate Listing, relu par la projection publique.
- `SubmitListing`, `SendToReview` et `PublishListing` sont les use cases certifiés qui mutent cet Aggregate.

## Rupture contractuelle

`ListingPublicationOrchestrationRequest` contient seulement `listingId`, `action` et `expectedVersion`. Les use cases Aggregate exigent en plus :

- un `ListingRevisionId` ;
- une `TransitionEvidence` complète : acteur, trigger, raison, origine et instant ;
- pour la publication, un `MediaCollectionId` et une `ExpirationDate`.

`ListingPublicationEventMetadata` ajoute uniquement `occurredAt` et `recordedAt`. Aucun port actuel ne fournit les autres éléments de manière autoritative.

Les générer ou les déduire dans un composite serait une nouvelle décision métier. Construire ou forcer l'Aggregate est interdit.

## Transaction

Le workflow et le Registry utilisent la même connexion PostgreSQL et pourraient techniquement participer à une transaction locale. Cette possibilité ne résout pas l'absence de données autoritatives et ne suffit donc pas à rendre la frontière recevable.
