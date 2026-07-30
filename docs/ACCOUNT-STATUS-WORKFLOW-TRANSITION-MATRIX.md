# Account Status Workflow — Transition Matrix

| État courant | Action | Décision | État résultant | Transition |
|---|---|---|---|---|
| `Active` | `Suspend` | `Applied` | `Suspended` | oui |
| `Active` | `Reactivate` | `AlreadyInState` | `Active` | non |
| `Suspended` | `Suspend` | `AlreadyInState` | `Suspended` | non |
| `Suspended` | `Reactivate` | `Applied` | `Active` | oui |

## Priorité

1. vérifier la cohérence structurelle du contexte;
2. vérifier identité, état, version observée et action préparés;
3. produire `InvalidContext` en cas de divergence;
4. sinon appliquer exactement la ligne état/action correspondante.

Les deux enums rendent les quatre combinaisons exhaustives. La décision
utilise des `match` fermés, sans `default`, exception métier ou branche
ouverte.

## Invariants du résultat

- `Applied` contient exactement une transition et expose son état cible;
- `AlreadyInState` conserve l'état courant et ne contient aucune transition;
- `InvalidContext` conserve l'état courant et ne contient aucune transition;
- une même entrée produit toujours un résultat égal.
