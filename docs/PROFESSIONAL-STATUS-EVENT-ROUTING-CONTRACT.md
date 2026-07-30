# Professional Status Event Routing Contract

Le port `ProfessionalStatusEventRouter` reçoit une enveloppe V1 et retourne un résultat fermé.

| Statut | Diagnostic | Acquittement |
|---|---|---|
| `Routed` | aucun | oui |
| `Deferred` | `RouteUnavailable` | non |
| `RetryableFailure` | `TransferFailed` | non |
| `Rejected` | `UnsupportedEvent` ou `CorruptedEvent` | non |

Aucune implémentation du port n'appartient à 4.5F.
