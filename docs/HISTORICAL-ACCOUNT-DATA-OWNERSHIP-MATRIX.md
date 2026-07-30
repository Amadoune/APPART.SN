# Historical Account — Data Ownership Matrix

| Donnée / décision | Owner | Persistance Account | Account Status 4.9 |
|---|---|---|---|
| existence Account | IdentityAccess / Account historique | autorité durable | lecture bootstrap seulement |
| accountId | Account | conserve et garantit unicité | référence |
| email / téléphone | Account | conserve, unicités durables | aucune autorité |
| nom | Account | conserve | aucune autorité |
| Credential | Account | conserve dans la transaction Account | orthogonal |
| Verification Email/Phone | Account | conserve dans la transaction Account | orthogonal |
| RoleAssignment historique | Account | conserve dans la transaction Account | facts-only hors lifecycle |
| Consent historique | Account | conserve dans la transaction Account | orthogonal |
| suspended historique | Account historique | conserve pour bootstrap uniquement | aucune autorité après bootstrap |
| version globale Account | AccountRegistry | concurrence `add/save` | provenance bootstrap uniquement |
| état lifecycle courant | journal 041 | aucune autorité | autorité exclusive |
| version lifecycle | journal 041 | aucune autorité | autorité exclusive |
| décision Suspend/Reactivate 4.9 | AccountStatusWorkflow | aucune | autorité exclusive |
| événements Account en attente | domaine Account | Repository ne publie pas | aucune |
| transaction Account | Repository PostgreSQL futur | propriétaire ou participante | partage possible au bootstrap |
| Runtime binding AccountRegistry | 4.9P-D | fournit la source | consommé indirectement |

## Clarification des sous-objets

Dans le modèle actuel, Credential, Verification, RoleAssignment et Consent sont
des composants possédés par l'agrégat, non de simples références :

- ils n'ont aucun repository propre;
- leur cycle est commandé par `Account`;
- ils participent au clone détaché;
- leur état complet est nécessaire à `Account::reconstitute()`.

La future persistance peut utiliser plusieurs tables physiques sans créer
plusieurs autorités transactionnelles.

## Règle de non-régression 4.9

Le chemin Account Status ne doit jamais appeler `Account::suspend()`,
`Account::reactivate()`, `SuspendAccount`, `ReactivateAccount` ou
`AccountRegistry::save()` pour appliquer une transition 4.9. Cette interdiction
évite toute double écriture entre l'ancien booléen et le journal 041.
