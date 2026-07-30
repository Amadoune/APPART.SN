# Lead Lifecycle Delivery Consumption Matrix

| Routage | Diagnostic | Consommation générique | Effet |
|---|---|---|---|
| `Routed` | aucun | `Consumed` | acquittement |
| `Deferred` | `RouteUnavailable` | `BlockedBySourceReadiness` | aucun acquittement, attente |
| `RetryableFailure` | `TransferFailed` | `RetryableFailure` | aucun acquittement, retry |
| `Rejected` | `UnsupportedEvent` | `UnsupportedEventType` | aucun acquittement, disposition explicite |
| `Rejected` | `CorruptedEvent` | `DivergentPayload` | aucun acquittement, quarantaine |

La matrice est fermée, sans branche `default`. Elle ne consomme aucun message et ne constitue pas un Consumer.
