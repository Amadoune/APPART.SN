# Search Rank Policy

## Audit

`SearchRank` valide uniquement un entier entre 0 et 10000. Il ne définit ni valeur initiale, ni formule, ni pondération.

`SearchProjectionPolicy` copie exactement `ListingProjectionSource::rank`. Aucun `RankPolicy`, calculator, adapter Listing productif ou champ persistant autoritatif n'a été trouvé.

Valeurs observées :

- `100` dans les fixtures de tests Domain ;
- `500` dans `CreateLocalFirstListing` ;
- `600` dans `CreateLocalPublicFactListing`.

Ces valeurs sont incompatibles entre elles et leurs contextes les rendent non normatives.

## Décision

**AUTORITÉ ABSENTE — FAIL-FAST.**

Aucune valeur minimale ne peut être retenue. Cette absence suffit au verdict NO GO.

## Completion 01

Ce constat reste historique. L'autorité corrective certifiée définit désormais : sens score de priorité, direction « plus grand = plus prioritaire », domaine 0..10000, baseline v1 `0` et policyId `public-search-ranking-policy-v1`.

Le futur matérialiseur obtient ce rang du contrat de policy ; aucune constante n'est placée dans l'orchestrateur.
