# Property Lifecycle Router Outcome Matrix

| Résultat destination | Résultat routeur | Diagnostic | Acquittement futur |
|---|---|---|---:|
| `Stored` | `Routed` | aucun | oui |
| `AlreadyStored` | `Routed` | aucun | oui |
| `Unavailable` | `Deferred` | `route_unavailable` | non |
| `RetryableFailure` | `RetryableFailure` | `transfer_failed` | non |
| `Rejected` | `Rejected` | `corrupted_event` | non |

Le mapping est fermé, sans branche `default`.
