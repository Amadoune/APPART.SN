# Lead Lifecycle Routing Result Matrix

| Résultat Inbox | Résultat routeur | Diagnostic | Acquittement |
|---|---|---|---|
| `Stored` | `Routed` | aucun | oui |
| `AlreadyStored` | `Routed` | aucun | oui, idempotent |
| `Unavailable` | `Deferred` | `RouteUnavailable` | non |
| `RetryableFailure` | `RetryableFailure` | `TransferFailed` | non |
| `Rejected` | `Rejected` | `CorruptedEvent` | non |
| exception technique | `RetryableFailure` | `TransferFailed` | non |

Une divergence pour un `messageId` existant est toujours `Rejected`. Elle ne peut jamais être assimilée à un rejeu idempotent.
