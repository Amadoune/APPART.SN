# Lead Lifecycle Workflow Decision Table

| État / Action | `Deliver` | `Reject` | `Close` | `Unknown` |
|---|---|---|---|---|
| `Created` | `Allowed → Delivered` | `Allowed → Rejected` | `Denied: TransitionForbidden` | `Denied: UnknownAction` |
| `Delivered` | `Denied: IncompatibleState` | `Denied: IncompatibleState` | `Allowed → Closed` | `Denied: UnknownAction` |
| `Rejected` | `Denied: IncompatibleState` | `Denied: IncompatibleState` | `Allowed → Closed` | `Denied: UnknownAction` |
| `Closed` | `Denied: TerminalState` | `Denied: TerminalState` | `Denied: TerminalState` | `Denied: UnknownAction` |

La table couvre les 16 couples possibles : 4 décisions autorisées et 12 décisions refusées. À couple identique, les objets retournés sont structurellement identiques.
