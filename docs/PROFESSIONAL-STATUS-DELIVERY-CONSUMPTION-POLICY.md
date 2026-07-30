# Professional Status Delivery Consumption Policy

| Routage | Consommation générique | Effet |
|---|---|---|
| `Routed` | `Consumed` | acquittement |
| `Deferred / RouteUnavailable` | `BlockedBySourceReadiness` | aucun acquittement |
| `RetryableFailure / TransferFailed` | `RetryableFailure` | retry générique |
| `Rejected / UnsupportedEvent` | `UnsupportedEventType` | disposition permanente |
| `Rejected / CorruptedEvent` | `DivergentPayload` | quarantaine |

La matrice est fermée et ne contient aucune branche `default`.
