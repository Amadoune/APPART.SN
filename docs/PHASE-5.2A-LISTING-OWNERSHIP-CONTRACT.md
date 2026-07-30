# Phase 5.2A — ListingOwnership Contract V1

## Invariants

- titulaire unique par Listing ;
- titulaire égal à l'owner Property lors de la création V1 ;
- AccountId issu de la session, jamais d'un claim ;
- une délégation ne transfère pas le titre ;
- permissions fermées : `VIEW`, `EDIT`, `SUBMIT` ;
- seul le titulaire accorde ou révoque ;
- Account indisponible : permission refusée.

## Commands

- `EstablishListingOwnership(intentId, ownerAccountId, listingId, propertyId)` ;
- `GrantListingDelegation(intentId, ownerAccountId, delegateAccountId,
  listingId, permissions, expectedVersion)` ;
- `RevokeListingDelegation(intentId, ownerAccountId, delegateAccountId,
  listingId, permissions, expectedVersion)`.

## Query

`AuthorizeListingAction(actorAccountId, listingId, action)` retourne
`Allowed(role, ownershipVersion)`, `Denied` ou `Unavailable`.

## Résultats de mutation

`Applied(version)`, `AlreadyApplied(version)`, `DivergentIntent`,
`NotFoundOrForbidden`, `DelegateUnavailable`, `InvalidPermission`,
`ConcurrentModification`, `DependencyUnavailable`.

Les rôles et consentements IAM ne sont ni modifiés ni réinterprétés.
