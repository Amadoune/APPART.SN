# Matrice Consumer multi-cibles

| Entrée | Résultat Delivery |
|---|---|
| `NoTargets` | `Consumed` |
| toutes cibles `AlreadyApplied` | `AlreadyConsumed` |
| au moins une cible `Applied`, toutes réussies | `Consumed` |
| source/readiness incomplète | `BlockedBySourceReadiness` |
| divergence | `DivergentPayload` |
| collision/conflit permanent | `PermanentFailure` |
| identité, ownership, corruption ou checkpoint invalide | `PermanentFailure` |

Après tout résultat non consommable, aucune cible suivante n'est appelée.
