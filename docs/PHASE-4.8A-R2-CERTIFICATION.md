# Phase 4.8A-R2 — Workflow Decision Boundary Amendment Certification

## Livrables

- `PHASE-4.8A-R2-WORKFLOW-DECISION-BOUNDARY-AMENDMENT.md` ;
- `PLACE-LIFECYCLE-DECISION-OWNERSHIP-MATRIX.md` ;
- mise à jour de `PHASE-4.8-ROADMAP.md`.

## Critères satisfaits par conception

- chaque décision possède un propriétaire unique ;
- la frontière entre Workflow, Inspection, Orchestration et Persistance est
  normative ;
- les quatre décisions bloquantes sont attribuées hors Workflow ;
- les observations d'Inspection restent distinctes des décisions applicatives ;
- `PlaceMergeContextV1` reste inchangé ;
- aucune couche ne reconstruit une responsabilité d'une autre couche ;
- aucune implémentation technique n'est introduite ;
- aucune fondation certifiée n'est modifiée.

## Répartition certifiable

| Couche | Décisions |
|---|---|
| Workflow | transitions, `AlreadyInState`, `TerminalState`, `SameIdentity`, `TargetDisabled`, `TargetMerged`, `DifferentType`, `DifferentCountry`, `InvalidContext` |
| Inspection | observations `Found`, `Missing`, `Corrupted` |
| Orchestration | `TargetMissing`, `ReplayConflict`, divergence et corruption |
| Persistance | `SourceVersionConflict`, `TargetVersionConflict` |

## Validation

Le sprint est exclusivement documentaire. Aucun test n'est requis pour
prétendre qu'une implémentation a été validée. La baseline 4.8A-R1 reste une
référence d'entrée, non une campagne nouvellement exécutée par cet amendement.

## Verdict

**GO CERTIFIÉ**.

Le blocage contractuel est levé et l'amendement est fermé. Le seul sprint
autorisé est **4.8B — Place Lifecycle Workflow Foundation**.
