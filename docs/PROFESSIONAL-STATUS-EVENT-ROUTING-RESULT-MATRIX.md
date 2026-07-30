# Professional Status Event Routing Result Matrix

| Inbox | Routage | Acquittement |
|---|---|---|
| `Stored` | `Routed` | oui |
| `AlreadyStored` | `Routed` | oui |
| `Unavailable` | `Deferred / RouteUnavailable` | non |
| `RetryableFailure` | `RetryableFailure / TransferFailed` | non |
| `Rejected` | `Rejected / CorruptedEvent` | non |

Une exception technique non typée est absorbée en `RetryableFailure`.
