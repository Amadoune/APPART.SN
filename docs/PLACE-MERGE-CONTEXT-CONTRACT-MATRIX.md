# Place Merge Context Contract Matrix

## Matrice de preuve

| Preuve reçue | Préservée par le contexte | Décision future possible |
|---|---:|---|
| source = cible | oui | `SameIdentity` |
| cible absente | résultat d'inspection `Missing` | `TargetMissing` |
| cible `Disabled` | oui | `TargetDisabled` |
| cible `Merged` | oui | `TargetMerged` |
| types différents | oui | `DifferentType` |
| pays différents | oui | `DifferentCountry` |
| version source divergente | version attendue explicite | `SourceVersionConflict` |
| version cible divergente | version observée explicite | `TargetVersionConflict` |
| même intention, même preuve | contrat de rejeu | `AlreadyApplied` |
| même intention, preuve différente | contrat de rejeu | `ContextDivergence` |
| inspection corrompue | résultat fermé | `InspectionCorrupted` |

Les décisions de la dernière colonne ne sont pas implémentées dans ce sprint.

## Matrice des dépendances

| Dépendance | Statut |
|---|---|
| Value Objects propriétaires `PlaceId`, `PlaceType`, `CountryCode` | autorisée |
| `PlaceRegistry` | interdite |
| projection publique | interdite |
| Workflow | interdit |
| persistance / PostgreSQL | interdite |
| Runtime / Laravel | interdit |
| Event / Transport / Routing / Outbox | interdits |
| horloge ou identité implicite | interdites |
