# Media Item Lifecycle Transition Matrix

| État | Remove | Archive | Unknown |
|---|---|---|---|
| Active | Allowed → Removed | Allowed → Archived | Denied → UnknownAction |
| Removed | Denied → TerminalState | Denied → TerminalState | Denied → UnknownAction |
| Archived | Denied → TerminalState | Denied → TerminalState | Denied → UnknownAction |

Les neuf couples sont fermés : deux transitions et sept refus.
