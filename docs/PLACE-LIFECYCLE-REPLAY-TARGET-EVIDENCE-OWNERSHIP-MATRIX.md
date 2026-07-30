# Place Lifecycle Replay and Target Evidence Ownership Matrix

| Frontière | Entrées | Sorties | Interdictions |
|---|---|---|---|
| Target Evidence Qualification | `targetId` + autorité d'existence | `TargetMissing` ou preuve qualifiée | rejeu, transition, persistance |
| Replay Inspection | `sourceId`, `intentId` | `Found`, `Missing`, `Corrupted` | existence cible, décision métier |
| Orchestration | V1 qualifié + inspection | rejeu/divergence/corruption ou séquencement | reconstruction cible, règles métier |
| Workflow | état + action + V1 | transition ou refus métier | inspection, concurrence durable |
| Persistance | transition + V1 | résultat d'écriture fermé | qualification cible, rejeu, métier |

## Règle normative

`Inspection::Missing` poursuit le chemin nominal. Seule Target Evidence
Qualification peut produire `TargetMissing`.

## Invariant d'entrée de 4.8E

```text
PlaceMergeContextV1 reçu
→ cible déjà qualifiée
→ TargetMissing impossible dans 4.8E
```
