# Media Item Lifecycle Event Routing Result Matrix

| Résultat Inbox | Résultat de routage | Diagnostic | Acquittement |
|---|---|---|---|
| `Stored` | `Routed` | aucun | oui |
| `AlreadyStored` | `Routed` | aucun | oui |
| `Unavailable` | `Deferred` | `RouteUnavailable` | non |
| `RetryableFailure` | `RetryableFailure` | `TransferFailed` | non |
| `Rejected` | `Rejected` | `CorruptedEvent` | non |

Toute exception technique non typée est absorbée en `RetryableFailure / TransferFailed`. Aucun autre résultat n'est exposé.
