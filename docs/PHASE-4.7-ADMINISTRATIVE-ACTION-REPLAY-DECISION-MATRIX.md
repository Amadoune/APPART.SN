# Phase 4.7C-R1 — Replay Decision Matrix

| Inspection exacte | Comparaison | Résultat |
|---|---|---|
| version différente de `expectedVersion + 1` | aucune reconstruction | `VersionConflict` |
| version exacte, action différente | transition inspectée | `TransitionDivergence` |
| version/action exactes, checksum différent | contexte intégral | `ContextDivergence` |
| version/action/checksum exacts | identité stricte | `AlreadyApplied` |
| source absente | résultat d'inspection | `Missing` |
| source invalide | résultat d'inspection | `Corrupted` |

L'application nominale reste :

```text
read → expectedVersion exact → Workflow → future persistance contextuelle
```

Le rejeu reste :

```text
read → expectedVersion + 1 → inspection exacte → politique de rejeu
```

Le Workflow n'est jamais rappelé et aucune transition n'est reconstruite sur le chemin de rejeu.
