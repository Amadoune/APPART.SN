# Public Property Promotion Authority 01 — Blueprint 01

## Décision d'ensemble

La future frontière est une orchestration applicative de `RealEstateCatalog`, distincte du Domain :

`Submit owner-scoped → PromoteAuthoredPropertyV1 → lecture PropertyAuthoringStore → RegisterProperty → PropertyRegistry`.

Elle demande la création ; `RegisterProperty`, `PropertyTypePolicy`, `GeographicPlaceCatalog` et `PropertyRegistry` conservent toute l'autorité de validation et de persistance de l'Aggregate.

## Décisions V1

- Moment : précondition synchrone du Submit, avant la transition Listing `Submitted`.
- Portée : création initiale seulement. Les mutations post-promotion ne sont pas synchronisées en V1.
- Entrée : identités et contrôle de concurrence uniquement ; aucun fait Property fourni par HTTP.
- Lecture : snapshot Authoring relu par l'orchestrateur.
- Transaction : transaction locale RealEstateCatalog couvrant ledger de promotion et création Domain ; aucune transaction distribuée avec Listing.
- Publication : impossible tant que la promotion n'est pas `Applied` ou `AlreadyApplied` compatible.
- Projection : lecture exclusive de `PropertyRegistry`.

## État du Blueprint

L'architecture est définie, mais le contrat n'est pas implémentable avec les données courantes. Surface, rooms, bathrooms, référence et adresse géographique structurée ne possèdent pas de source autoritative disponible dans `PropertyAuthoringState`. La promotion doit retourner `IncompleteAuthoring` et ne rien muter.

**Verdict : NO GO PROPOSÉ.**
