# Phase 5.2A — Frozen Capability Specification

## F-19 — Property & Listing Authoring

Le gel actif et exécutoire couvre :

- les quatre autorités et leurs contrats ;
- `CreateListingDraftV1` ;
- états, résultats fermés, checksums et règles d’idempotence ;
- persistence, mappers, stores, concurrence et rollback ;
- Runtime, Availability et diagnostics ;
- HTTP privé et sécurité ;
- orchestrateur Operations et handoff F-01 ;
- façade Public Authoring, routes versionnées et UI ;
- tests et garanties architecturales certifiés.

## F-20 — Migrations Authoring 055–057

Le gel actif et exécutoire couvre l’ordre, les schémas, tables, colonnes, contraintes,
rollback, absence de FK cross-domain et absence de cascade des migrations :

- `055_listing_creation_intents` ;
- `056_property_authoring` ;
- `057_listing_authoring`.

## Gouvernance

F-19 et F-20 sont certifiés, actifs et exécutoires depuis le GO FINAL 5.2A.
Toute évolution requiert un amendement versionné.
Une nouvelle migration additive postérieure à 057 reste possible pour une
nouvelle capacité, sans réécriture de 055–057.
