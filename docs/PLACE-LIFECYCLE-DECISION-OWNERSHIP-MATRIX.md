# Place Lifecycle Decision Ownership Matrix

| Couche | Possède | Ne possède jamais |
|---|---|---|
| Workflow | transitions, état identique, terminalité, identité, état cible observé, compatibilité type/pays, contexte métier invalide | existence durable, rejeu, concurrence |
| Inspection | `Found`, `Missing`, `Corrupted` | transition ou décision métier |
| Orchestration | `TargetMissing`, corruption, `ReplayConflict`, divergence, séquencement | règles d'état/type/pays, conflits au commit |
| Persistance | `SourceVersionConflict`, `TargetVersionConflict` | validité métier, rejeu, orchestration |

## Règle d'unicité

Pour une requête donnée, la première couche propriétaire produisant une issue
fermée termine la décision. Les couches suivantes ne sont pas appelées. Aucune
issue n'a deux propriétaires.

## Matrice des quatre décisions bloquantes

| Décision | Pourquoi le Workflow ne la possède pas | Owner définitif |
|---|---|---|
| `TargetMissing` | V1 contient une preuve observée et ne prouve pas l'existence courante | Orchestration, depuis `Inspection::Missing` |
| `SourceVersionConflict` | exige la version durable au commit | Persistance |
| `TargetVersionConflict` | exige la version durable de la cible au commit | Persistance |
| `ReplayConflict` | exige une inspection d'une tentative antérieure | Orchestration |
