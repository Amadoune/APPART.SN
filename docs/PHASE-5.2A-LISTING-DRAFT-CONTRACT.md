# Phase 5.2A — ListingAuthoringDraft Contract V1

## Owner et invariants

`ListingAuthoringDraft` possède le contenu privé éditorial. `Listing` reste
owner de l'identité, du PropertyId et du statut lifecycle. Un draft référence
exactement un Listing et un Property ; ces liens sont immuables en V1.

## Commands

| Command | Données minimales |
|---|---|
| `CreateListingDraft` | intentId, actorAccountId, listingId, propertyId, title, transactionKind, requestedAt |
| `PatchListingDraft` | intentId, actorAccountId, listingId, expectedVersion, patch fermé, requestedAt |
| `DiscardListingDraft` | intentId, actorAccountId, listingId, expectedVersion, requestedAt |
| `RequestListingSubmission` | intentId, actorAccountId, listingId, expectedVersion, requestedAt |

Le patch fermé V1 peut porter title, description, transactionKind, price,
currency, charges, availabilityDate et contactPreference. Les champs inconnus
ou `null` ambigus sont refusés.

## Queries

- `GetListingDraft(listingId, actorAccountId)` ;
- `AssessListingCompleteness(listingId, actorAccountId)`.

Résultats : `Found`, `NotFoundOrForbidden`, `Unavailable`.

## Résultats de mutation

`Applied(version)`, `AlreadyApplied(version)`, `DivergentIntent`,
`NotFoundOrForbidden`, `InvalidContent`, `Incomplete(missingCodes)`,
`ConcurrentModification`, `LifecycleConflict`, `DependencyUnavailable`.

## Historique

Chaque mutation appliquée crée une révision append-only avec version, intentId,
actorAccountId, champs modifiés et instant. Les valeurs sensibles antérieures
ne sont pas publiées dans les événements.
