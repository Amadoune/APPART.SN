# Media Item Lifecycle Delivery Consumption Matrix

| Routage | Diagnostic | Consommation générique | Acquittement | Effet |
|---|---|---|---|---|
| `Routed` | aucun | `Consumed` | oui | livraison durable confirmée |
| `Deferred` | `RouteUnavailable` | `BlockedBySourceReadiness` | non | attente de disponibilité |
| `RetryableFailure` | `TransferFailed` | `RetryableFailure` | non | retry générique |
| `Rejected` | `UnsupportedEvent` | `UnsupportedEventType` | non | quarantaine contractuelle |
| `Rejected` | `CorruptedEvent` | `DivergentPayload` | non | quarantaine pour divergence |

Aucune branche implicite ou `default` n'existe. Un résultat `Rejected` sans diagnostic certifié est une violation contractuelle.
