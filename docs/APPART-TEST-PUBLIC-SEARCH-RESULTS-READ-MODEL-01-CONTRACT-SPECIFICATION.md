# APPART.TEST Public Search Results Read Model 01 — Contract Specification

## Endpoint

`GET /api/public-search/results`

Paramètres fermés :

- `limit` : entier optionnel, 1 à 24, défaut 12 ;
- `after` : chemin canonique optionnel de forme `annonces/{slug}`.

Tout champ inconnu est rejeté en HTTP 422.

## Contrat Application

`PublicSearchResultsReaderV1::read(PublicSearchResultsQuery): PublicSearchResultsResult`

Statuts fermés :

- `available` ;
- `empty` ;
- `corrupted` ;
- `dependency_unavailable`.

`available` et `empty` produisent HTTP 200. Les états fail-closed produisent HTTP 503.

## Résultat

Le résultat contient exclusivement `status`, `items` et `nextCursor`. Chaque item est un `PublicSearchListingSummary` issu d'un `PublicListingReadModel` validé.
