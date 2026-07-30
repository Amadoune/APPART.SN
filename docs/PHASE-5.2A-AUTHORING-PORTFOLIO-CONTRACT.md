# Phase 5.2A — AuthoringPortfolio Contract V1

## Nature

`AuthoringPortfolio` est une projection privée, reconstruisible et sans
commande métier. Elle ne devient jamais owner des statuts.

## Queries

- `ListAuthoringPortfolio(actorAccountId, cursor, limit, filters)` ;
- `GetAuthoringPortfolioItem(actorAccountId, listingId)`.

Filtres fermés : relation (`OWNER`, `DELEGATE`), draft presence, Property
availability et Listing lifecycle state. `limit` est borné à 100.

## Résultats

- `Page(items, nextCursor, sourceCheckpoint)` ;
- `Found(item, sourceCheckpoint)` ;
- `NotFoundOrForbidden` ;
- `Unavailable`.

Un item contient seulement IDs, relation, versions, code de complétude et vues
minimales des états canoniques lus. Aucun email, téléphone, adresse complète,
token ou diagnostic interne.

## Fraîcheur et reconstruction

Le checkpoint est monotone. Un retard de projection est explicite ; il ne
modifie jamais une décision d'autorisation, laquelle relit ListingOwnership.
