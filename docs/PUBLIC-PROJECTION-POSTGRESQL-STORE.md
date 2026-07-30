# Public Projection PostgreSQL Store

## Composants

- `PostgreSqlPublicListingProjectionMapper` sérialise et valide records, watermarks et read models ;
- `PostgreSqlPublicListingProjectionReader` fournit query publique exacte et lectures techniques ciblées ;
- `PostgreSqlPublicListingProjectionWriter` implémente les quatre intentions d'écriture ;
- `PostgreSqlPublicListingProjectionStore` compose les ports Query et Writer ;
- migration `006_public_listing_projection.sql` possède schéma, tables, contraintes et index.

## Invariants

Le Store est l'unique source durable des projections publiques. Les candidats restent invisibles. Historical réserve définitivement son canonical. Tombstone n'est jamais servi. Un watermark incomplet est refusé, un ancien est rejeté, un incomparable diverge et un doublon identique est idempotent.

Le remplacement canonical est atomique. Une collision conserve tous les records existants. Une transaction appelante peut inclure l'écriture et obtenir un rollback commun.

## Limites

Aucun binding Laravel, Runtime, HTTP, source Updater, rebuild, supervision, métrique ou purge n'est introduit. Ces responsabilités appartiennent aux Sprints 3.6E–3.6G.
