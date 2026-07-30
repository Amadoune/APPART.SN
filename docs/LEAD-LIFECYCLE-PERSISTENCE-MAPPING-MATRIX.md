# Lead Lifecycle Persistence Mapping Matrix

| Entrée | `version` | `previous_state` | `current_state` | `action` |
|---|---:|---|---|---|
| Initialisation | 1 | `NULL` | état fourni | `NULL` |
| Transition certifiée | version fournie | `transition.from` | `transition.to` | `transition.action` |

Le checksum canonique est le SHA-256 de six valeurs séparées par `LF`, dans cet ordre : `lead_id`, `version`, `previous_state`, `current_state`, `action`. Une valeur nulle est représentée par une chaîne vide.

La reconstruction ne produit que `LeadLifecycleStoredState(LeadId, LeadLifecycleState, version)`. Une ligne invalide ou un checksum divergent devient un résultat de lecture `Corrupted`.
