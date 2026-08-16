# Frontière du prochain chantier

## Chantier minimal

**APPART.SN SEARCH DISCOVERY — PUBLIC SEARCH DECISION MATERIALIZATION AUTHORITY 01**.

Cette autorité doit décider, sans UI :

1. le handoff entrant depuis les faits publics Published ;
2. le catalogue exact d'événements ou la commande owner-scoped ;
3. la construction SearchDiscovery de `ProjectionState`, `SearchRank`, facettes et `SourceRevisionSet` ;
4. l'identité et la version de `SearchDecision` ;
5. idempotence, replay, concurrence et transaction owner-locale ;
6. le résultat fermé attendu par l'orchestrateur ;
7. le séquencement garanti avant `ProjectPublishedListingV1` ;
8. le rattrapage autorisé d'un Listing déjà Published sans SQL ni fixture.

## Options non retenues sans amendment

- retirer `searchVersion` du watermark ;
- fabriquer une version dans Projection ;
- déduire la décision depuis `listing_projections` ;
- faire écrire PublicationReview dans Search ;
- réutiliser les commandes locales de démonstration pour RC2.

Ces options modifieraient une autorité certifiée ou créeraient un cycle.

## Chantier UI séparé

La réduction `NotReady → confirmed` doit faire l'objet d'un handoff HTTP/UI distinct. Elle ne conditionne pas la décision sur la source Search.
