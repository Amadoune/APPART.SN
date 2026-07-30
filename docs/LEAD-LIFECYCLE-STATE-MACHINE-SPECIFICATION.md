# Lead Lifecycle State Machine Specification

## États fermés

| État | Rôle | Terminal |
|---|---|---:|
| `Created` | Entrée explicite du cycle | Non |
| `Delivered` | Lead transmis avec succès | Non |
| `Rejected` | Lead refusé | Non |
| `Closed` | Cycle définitivement clôturé | Oui |

## Actions fermées

`Deliver`, `Reject`, `Close` sont les actions métier exécutables. `Unknown` permet de représenter explicitement une action non reconnue et conduit toujours à un refus.

```text
Created --Deliver--> Delivered --Close--> Closed
       \--Reject---> Rejected  --Close--> Closed
```

`initialState()` retourne toujours `Created`. Aucune autre entrée et aucune sortie depuis `Closed` ne sont autorisées.
