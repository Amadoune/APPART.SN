# Professional Status Replay Outcome Matrix

| Version inspectée | Action du snapshot | Acteur et instant | Résultat |
|---|---|---|---|
| `expectedVersion + 1` | identique | identiques | `AlreadyApplied` |
| `expectedVersion + 1` | identique | au moins un différent | `ContextDivergence` |
| différente | toute valeur | toute valeur | `Conflict` |
| attendue | différente de l'action demandée | toute valeur | `Conflict` |

La comparaison porte exclusivement sur l'action demandée et la transition exacte du snapshot inspecté. Aucun appel au workflow et aucune matrice de transitions ne participent au rejeu.
