# Result Catalog

| Statut | Sémantique |
|---|---|
| `Available` | Au moins un item sélectionnable est retourné ; cursor suivant éventuel |
| `Empty` | Requête valide et source disponible, mais aucun item sélectionnable |
| `Missing` | Le parent explicitement demandé n'existe pas |
| `Corrupted` | Une donnée source ne peut pas être reconstituée ou viole le modèle Geography |
| `DependencyUnavailable` | Registry/store/transaction de lecture indisponible |

Le catalogue applicatif est exhaustif. Les paramètres invalides sont refusés avant appel par l'adapter (HTTP 422) et ne deviennent pas un statut métier. `Empty` n'est jamais converti en indisponibilité.
