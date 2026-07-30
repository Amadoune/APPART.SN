# Phase 5.2A — PropertyAuthoring Contract V1

## Owner et invariants

`PropertyAuthoring` est owner de l'association Account–Property et de ses
intents. `Property` reste owner des faits immobiliers. Un Property possède un
owner actif unique ; l'owner est un `AccountId` stable et disponible.

## Commands

| Command | Données minimales |
|---|---|
| `InitiatePropertyAuthoring` | intentId, actorAccountId, propertyId, propertyReference, faits initiaux, requestedAt |
| `UpdatePropertyFacts` | intentId, actorAccountId, propertyId, expectedVersion, patch fermé, requestedAt |
| `ChangePropertyAddress` | intentId, actorAccountId, propertyId, expectedVersion, PlaceId, adresse, requestedAt |
| `ArchivePropertyAuthoring` | intentId, actorAccountId, propertyId, expectedVersion, requestedAt |

Le patch fermé autorise uniquement type, surface, rooms, bathrooms et
constructionYear. `propertyReference` et owner sont immuables en V1.

## Query

`GetPropertyAuthoring(propertyId, actorAccountId)` retourne `Found`,
`NotFoundOrForbidden` ou `Unavailable`.

## Résultats de mutation

`Applied(version)`, `AlreadyApplied(version)`, `DivergentIntent`,
`NotFoundOrForbidden`, `AccountUnavailable`, `PlaceUnavailable`,
`InvalidFacts`, `ConcurrentModification`, `LifecycleConflict`,
`DependencyUnavailable`.

## Concurrence

La réservation owner/Property est atomique. Les mises à jour utilisent
`expectedVersion`. Aucun résultat concurrent ne peut changer l'owner ou créer
deux Property pour le même `propertyId`.
