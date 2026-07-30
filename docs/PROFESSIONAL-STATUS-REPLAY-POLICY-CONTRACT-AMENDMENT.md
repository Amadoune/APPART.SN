# Professional Status Replay Policy Contract Amendment

## Cause

La politique 4.5C-R1 exigeait une transition demandée indisponible sur le chemin de rejeu. Sa construction aurait imposé un rappel du workflow ou une reconstruction interdite.

## Signature amendée

```text
classify(
    ProfessionalStatusAction requestedAction,
    ProfessionalStatusTransitionContext requestedContext,
    ProfessionalStatusContextualAppendInspection inspected
) → ProfessionalStatusReplayOutcome
```

La politique compare l'action demandée à l'action portée par la transition exacte du snapshot. Elle ne crée jamais de transition et ne connaît aucun état métier.

## Invariants

- `snapshot.version` doit être `expectedVersion + 1` ;
- action différente : `Conflict` ;
- action identique et contexte différent : `ContextDivergence` ;
- action, version et contexte identiques : `AlreadyApplied`.

Les résultats et tous les autres contrats 4.5C-R1/R2 restent inchangés.
