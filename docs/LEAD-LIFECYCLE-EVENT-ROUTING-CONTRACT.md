# Lead Lifecycle Event Routing Contract

Le port `LeadLifecycleEventRouter` reçoit exclusivement une `LeadLifecycleTransportEnvelope` et retourne un `LeadLifecycleEventRoutingResult`.

| Statut | Diagnostic | Acquittement |
|---|---|---|
| `Routed` | aucun | oui |
| `Deferred` | `RouteUnavailable` | non |
| `RetryableFailure` | `TransferFailed` | non |
| `Rejected` | `UnsupportedEvent` ou `CorruptedEvent` | non |

La construction privée et les fabriques ferment les couples statut–diagnostic. Aucun résultat autre que `Routed` n'autorise l'acquittement. Aucune implémentation du port n'appartient à ce sprint.
