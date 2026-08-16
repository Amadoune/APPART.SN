# Preuve RC2 courante

## Identité

Listing : `979cd5aa-ced1-48a1-8adf-8b29c843a0c2`.

## Lecture exacte

Le reader exécute :

```sql
SELECT decision_id, listing_id, version, state, payload, payload_checksum
FROM search_discovery.public_search_decisions
WHERE listing_id = :listing_id
```

La clé liée est le ListingId ci-dessus. L'inspection PostgreSQL read-only retourne zéro ligne. Le Listing Aggregate est pourtant `published` version 3 et la Property canonique est présente.

`PostgreSqlSearchDecisionReader` retourne donc `Missing`; `CertifiedPublicListingProjectionSource` le réduit en `SearchMissing`; l'Updater retourne `SourceUnavailable`; F3 retourne `NotReady`.

La table `public_projection.listing_projections` contient également zéro ligne pour ce Listing. Aucune insertion compensatoire n'a été faite.

Les tables plus récentes `search_owner_revision_journal` / `search_owner_current_index` ne sont pas les stores interrogés par cette frontière et ne sont pas déployées dans la base applicative observée. Elles n'expliquent donc pas directement `SearchMissing`.
