# Professional Status Replay Policy Compatibility Matrix

| Version inspectée | Action inspectée | Contexte | Résultat |
|---|---|---|---|
| `expectedVersion + 1` | identique à la demande | identique | `AlreadyApplied` |
| `expectedVersion + 1` | identique à la demande | acteur ou instant différent | `ContextDivergence` |
| différente | toute action | tout contexte | `Conflict` |
| attendue | différente de la demande | tout contexte | `Conflict` |

La matrice ne contient aucune décision de workflow. Elle qualifie uniquement un rejeu à partir des données explicitement demandées et inspectées.
