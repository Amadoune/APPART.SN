# Account Status — Historical Authority Matrix

| Donnée / décision | Autorité avant amorçage | Autorité après amorçage |
|---|---|---|
| existence du compte | source historique via frontière de persistance | identique |
| statut initial legacy | `Account::isSuspended()` | aucune autorité courante |
| statut courant lifecycle | absent | journal Account Status |
| version globale Account | agrégat historique | agrégat historique, hors lifecycle |
| version Account Status | absente | journal Account Status |
| transition métier | aucun nouveau chemin | `AccountStatusWorkflow` |
| application durable | aucune | future Persistance lifecycle |
| historique des anciennes mutations | événements Account historiques | historique figé |
| nouvelles intentions | aucune | future Inspection, hors 4.9C-R1 |

## Ports

| Port / source | Rôle normatif |
|---|---|
| `AccountRegistry` | port historique; existence et données hors lifecycle |
| futur port lifecycle | unique port durable du statut et de sa version |
| projection | lecture aval uniquement, jamais autorité |
| événement | fait publié, jamais commande ni autorité courante |

`AccountRegistry` ne reste donc pas l'unique port durable de l'ensemble
IdentityAccess. Il reste inchangé et propriétaire de son périmètre historique;
le futur port lifecycle possède exclusivement le nouveau statut.
