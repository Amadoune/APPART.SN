# Reservation Lifecycle Workflow Decision Table

Légende : `A:<état>` = transition autorisée ; `I` = `IncompatibleState` ; `F` = `TransitionForbidden` ; `T` = `TerminalState` ; `U` = `UnknownAction`.

| État / Action | Submit | Confirm | Reject | Start | Complete | Cancel | Expire | Unknown |
|---|---|---|---|---|---|---|---|---|
| `Draft` | A:`Requested` | F | F | F | F | A:`Cancelled` | F | U |
| `Requested` | I | A:`Confirmed` | A:`Rejected` | F | F | A:`Cancelled` | A:`Expired` | U |
| `Confirmed` | F | I | F | A:`InProgress` | F | A:`Cancelled` | A:`Expired` | U |
| `InProgress` | F | F | F | I | A:`Completed` | A:`Cancelled` | F | U |
| `Completed` | T | T | T | T | T | T | T | U |
| `Cancelled` | T | T | T | T | T | T | T | U |
| `Expired` | T | T | T | T | T | T | T | U |
| `Rejected` | T | T | T | T | T | T | T | U |

La table contient 64 décisions : 11 autorisées et 53 refusées. Elle est déterministe et indépendante de toute horloge ou donnée externe.
