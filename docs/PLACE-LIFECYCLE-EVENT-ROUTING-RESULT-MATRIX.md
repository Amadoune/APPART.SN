# Place Lifecycle Event Transport — Résultats fermés du port de routage

| Statut | Diagnostic | Acquitte la livraison |
|---|---|---|
| `Routed` | aucun | oui |
| `Deferred` | `RouteUnavailable` | non |
| `RetryableFailure` | `TransferFailed` | non |
| `Rejected` | `UnsupportedEvent` | non |
| `Rejected` | `CorruptedEvent` | non |

Le port ne définit aucune destination et n'exécute aucun transfert.
