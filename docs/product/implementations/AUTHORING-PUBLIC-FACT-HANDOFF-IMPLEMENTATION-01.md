# AUTHORING TO PUBLIC FACT HANDOFF IMPLEMENTATION 01

## Décision de frontière

`transactionKind` reste un fait privé de `ListingDraftState` avant Submit. `PropertyListingAuthoringOperations` fige au Submit un `AuthoringPublicFactSnapshot` owner-scoped. `PublishListing` ne scelle ce candidat qu'au moment de `ApproveAndPublish`. La projection publique ne lit ensuite que le fait scellé par `AuthoringPublicFactHandoffV1`.

Chaîne matérialisée :

`ListingDraftState` → Submit / snapshot candidat → ApproveAndPublish / scellement → `ListingPublished` → projection publique → `PublicSearchResultsReaderV1` → HTTP.

## Contrat et persistance

- `AuthoringPublicFactHandoffV1` expose exclusivement `prepare`, `candidate`, `seal` et `published`.
- Le snapshot canonique porte uniquement listing, version Authoring, transaction, identité/checksum source et instant observé.
- Le repository PostgreSQL est owner-scoped, verrouillé par advisory lock transactionnel, idempotent et compatible avec les transactions externes par savepoint.
- La migration additive 093 crée le handoff et ajoute des colonnes publiques nullable. Aucune migration historique n'est modifiée.

## Compatibilité

- Les annonces historiques conservent `transaction_kind = NULL` (`publicFacts = unqualified`).
- Aucun défaut `sale` ou `rent`, aucune rétroqualification et aucun backfill.
- Elles restent consultables, lisibles et indexables, mais sont exclues lorsqu'un filtre transaction est demandé.
- P02 reste inchangée et non qualifiée.

## Recherche

`PublicSearchResultsQuery` accepte uniquement `transaction`, `city`, `propertyType`, `limit` et `afterCanonicalPath`. Le repository applique transaction, ville et type dans le SQL de la projection active avant `ORDER BY` et `LIMIT`. Aucun accès Authoring, Aggregate ou SQL UI n'est introduit.
