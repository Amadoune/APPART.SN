# Place Lifecycle Workflow Decision Table

## Matrice état / action

| État | `Enable` | `Disable` | `Merge` avec contexte valide |
|---|---|---|---|
| `Enabled` | `AlreadyInState` | `Applied → Disabled` | `Applied → Merged` |
| `Disabled` | `Applied → Enabled` | `AlreadyInState` | `Applied → Merged` |
| `Merged` | `TerminalState` | `TerminalState` | `TerminalState` |

## Précédence des décisions

1. identité de l'état courant différente de la source V1 :
   `InvalidContext`;
2. état courant `Merged` : `TerminalState`;
3. pour `Enable` et `Disable` : matrice état/action uniquement;
4. pour `Merge` :
   - source égale à cible : `SameIdentity`;
   - cible observée `Disabled` : `TargetDisabled`;
   - cible observée `Merged` : `TargetMerged`;
   - types observés différents : `DifferentType`;
   - pays observés différents : `DifferentCountry`;
   - sinon : `Applied → Merged`.

L'ordre est normatif et rend les combinaisons cumulant plusieurs refus
totalement déterministes.

## Décisions exclues par l'amendement R2

Le Workflow ne contient jamais :

- `TargetMissing`;
- `SourceVersionConflict`;
- `TargetVersionConflict`;
- `ReplayConflict`;
- `ContextDivergence`;
- `InspectionCorrupted`.

Ces issues appartiennent respectivement à l'Orchestration ou à la Persistance.
