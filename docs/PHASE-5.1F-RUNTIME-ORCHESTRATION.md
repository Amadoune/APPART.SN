# Phase 5.1F — Runtime Orchestration

## Statut

Jalon ouvert, implémenté et proposé à la certification. Le GO relève exclusivement de l'autorité.

## Owner et composition

`IdentityAccessOrchestrator` est l'unique owner de coordination. Il ne remplace aucun owner métier et ne porte aucun invariant d'agrégat. `DeterministicIdentityAccessOrchestrator` expose huit points d'entrée fermés :

| Entrée | Opération |
|---|---|
| `authenticate` | Authentication |
| `manageSession` | Session |
| `recoverPassword` | PasswordRecovery |
| `changeContact` | ContactChange |
| `swapClaim` | ClaimSwap |
| `mutateProfile` | ProfileMutation |
| `closeAccount` | AccountClosure |
| `reopenAccount` | Reopen |

Chaque entrée exige une commande portant un `intentId` UUID, un checksum SHA-256, un `AccountId`, une opération et une date explicite. Une discordance est refusée avant toute écriture.

## Frontières

- Application définit commandes, résultats et ports.
- Infrastructure possède PDO, transaction, savepoint, advisory lock et journal.
- Laravel ne fait que lier les ports à leurs implémentations singleton.
- Aucun HTTP, Event, Delivery ou Outbox n'est introduit.
- Account Availability 5.1E reste une politique de lecture pure.
