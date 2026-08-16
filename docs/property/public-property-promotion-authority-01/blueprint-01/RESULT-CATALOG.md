# Result Catalog

| Statut fermé | Sémantique | Mutation |
|---|---|---|
| `Applied` | Aggregate créé et ledger validé | Oui, une fois |
| `AlreadyApplied` | Même commandId/checksum, ou Aggregate existant démontré compatible avec la même promotion | Non |
| `AuthoringMissing` | Aucun snapshot pour propertyId | Non |
| `OwnershipMismatch` | Session/commande et snapshot divergent | Non |
| `IncompleteAuthoring` | Au moins un invariant requis sans source | Non |
| `VersionConflict` | Version Authoring observée différente de l'attendue | Non |
| `DomainRejected` | Value Object, place ou policy Domain refuse les faits complets | Non, rollback local |
| `DependencyUnavailable` | Store, Registry, Geography, ledger ou transaction indisponible | Non/rollback ; retry permis |
| `DivergentCommand` | Même commandId avec checksum différent, ou Aggregate existant incompatible | Non |

Le catalogue est exhaustif en V1. Le Submit ne peut continuer que pour `Applied` ou `AlreadyApplied` compatible.
