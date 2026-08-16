# Rapport de compatibilité

| Périmètre | Effet de la décision |
|---|---|
| ListingLifecycle | inchangé ; reste owner de Published |
| RealEstateCatalog | inchangé ; fournit les faits Property |
| Media | inchangé ; fournit collection et révision |
| SearchDiscovery | ownership confirmé ; aucune écriture effectuée |
| Public Projection | contrat et watermark inchangés |
| PublicationReview F3 | `NotReady` reste correct ; aucune écriture Search ajoutée |
| Search UX/API | non ouvert et non testé |
| RC2 Listing | préservé Published |
| PostgreSQL | lectures uniquement ; aucune migration |
| UI | faux positif qualifié, non corrigé |

La stratégie recommandée est additive : un handoff et un matérialiseur SearchDiscovery versionnés. Elle ne requiert pas de modifier les Aggregates ni de faire de Search un consumer du Projection Store pour sa propre décision amont.

Aucune donnée sensible, session ou credential n'a été inspecté. Aucun staging, commit ou tag n'est effectué.
