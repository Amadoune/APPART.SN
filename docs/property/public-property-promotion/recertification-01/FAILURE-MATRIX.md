# Matrice des échecs

| Échec | Statut F6 | Mutation Property | Mutation ledger | Transition Listing | Retry |
|---|---|---:|---:|---:|---|
| Authoring absent | `AuthoringMissing` | non | non | non | après restauration source |
| owner différent | `OwnershipMismatch` | non | non | non | commande owner correcte |
| snapshot incomplet | `IncompleteAuthoring` | non | non | non | après complétion/version nouvelle |
| version divergente | `VersionConflict` | non | non | non | relire puis nouvelle commande |
| Geography/Domain refusé | `DomainRejected` | non | non | non | après état Domain admissible |
| dépendance/transaction indisponible | `DependencyUnavailable` | rollback | non | non | même commande permis |
| même commandId divergent | `DivergentCommand` | non | non | non | jamais automatiquement |
| replay identique réussi | `AlreadyApplied` | non supplémentaire | non supplémentaire | peut poursuivre | oui |
| workflow Listing échoue après Promotion | échec Authoring/Listing | Property durable | succès durable | **indéterminée : Aggregate peut être avancé sans Workflow** | **non certifié** |

La dernière ligne est l’unique divergence bloquante F7.
