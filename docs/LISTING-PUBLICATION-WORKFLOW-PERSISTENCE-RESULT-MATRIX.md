# Workflow Persistence Result Matrix

| Situation | Résultat |
|---|---|
| première initialisation | `Applied` |
| réémission initiale ou transition identique | `AlreadyApplied` |
| version absente, ancienne ou non contiguë | `RejectedVersion` |
| état initial divergent ou source différente du courant | `StateConflict` |
| triplet refusé par la contrainte certifiée | `TransitionRejected` |
| lecture sans ligne | `Missing` |
| lecture valide | `Found(snapshot)` |
| checksum ou valeur illisible | `Corrupted` |

Tous les refus laissent le journal inchangé.
