# Implementation Boundary

Future `AUTHORING DRAFT RESUME IMPLEMENTATION 01` uniquement :

1. contrat, DTO, statuts et composition read-only `AuthoringDraftResumeReaderV1` ;
2. binding/provider nominatif ;
3. route/controller `GET /authoring/workspace/{listingId}` ;
4. bootstrap du workspace et mode `resume` dans `authoring.js` ;
5. sélection Portfolio minimale ;
6. dérivation déterministe du step ;
7. éventuellement une lecture binaire Media owner-scoped si la Preview réelle est exigée ;
8. tests ciblés.

Exclus : migration, Domain Property, F6/F7, Promotion, Submit, Publication Review, Projection, Search, nouvelle identité métier et persistance du step.

La composition doit réutiliser les stores existants. Aucun SQL Application. Le mode nouveau parcours conserve son comportement actuel.
