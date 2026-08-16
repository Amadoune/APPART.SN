# Preuve de la matrice d’existence Property

| État initial | Verdict Promotion | Mutation |
|---|---|---|
| Property absente, commande valide | `Applied` | une Property et un ledger atomiques |
| Ledger identique | `AlreadyApplied` | aucune nouvelle mutation |
| Même commandId, checksum divergent | `DivergentCommand` | aucune mutation |
| Property ledgerless canonique | `AlreadyApplied` | aucune mutation, aucun ledger de rattrapage |
| Property ledgerless incompatible | `DivergentCommand` | Property intacte, aucun ledger de succès |

Les preuves PostgreSQL confirment une seule Property dans tous les chemins d’existence et l’absence de ledger dans le cas AddressId divergent.
