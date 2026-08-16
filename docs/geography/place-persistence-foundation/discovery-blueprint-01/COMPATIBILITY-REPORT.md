# Compatibility Report

| Boundary | Préservation |
|---|---|
| Geography Domain | Place et invariants inchangés |
| PlaceRegistry | contrat inchangé, adapter nouveau |
| Geography Lifecycle | tables et workflow inchangés |
| Geography Selection | port read-only dédié sur la source owner-local |
| Public Geography | reste dérivé/aval, aucune reprise |
| Property Authoring | non modifié |
| RealEstateCatalog | continue à valider par son catalog |
| Projection/Search | consumers uniquement |

La migration est additive vide, sans backfill, nouvelle hiérarchie, suppression historique ou transaction distribuée.

Stratégie de tests future : Unit mapper/reconstruction ; contrats add/find/save ; PostgreSQL réel pour unicité, hierarchy, merge, aliases, locking et rollback ; Architecture pour ownership ; Feature pour binding ; migration up/down ciblée.
