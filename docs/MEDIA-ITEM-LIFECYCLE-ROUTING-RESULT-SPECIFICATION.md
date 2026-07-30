# Media Item Lifecycle Routing Result Specification

| Statut | Diagnostic | Acquittement |
|---|---|---|
| `Routed` | aucun | autorisé |
| `Deferred` | `RouteUnavailable` | interdit |
| `RetryableFailure` | `TransferFailed` | interdit |
| `Rejected` | `UnsupportedEvent` | interdit |
| `Rejected` | `CorruptedEvent` | interdit |

Le port `MediaItemLifecycleEventRouter` retourne obligatoirement un résultat fermé. Aucune implémentation concrète n'est introduite en 4.6F.
