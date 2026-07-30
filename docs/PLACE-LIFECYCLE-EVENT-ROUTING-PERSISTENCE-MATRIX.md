# Place Lifecycle Event Routing — Matrice de persistance

| Observation durable | Résultat store | Résultat routage |
|---|---|---|
| message absent, insertion valide | `Stored` | `Routed` |
| message présent, contenu exact | `AlreadyStored` | `Routed` |
| message présent, contenu divergent | `Rejected` | `Rejected/CorruptedEvent` |
| destination indisponible | `Unavailable` | `Deferred/RouteUnavailable` |
| erreur technique transitoire | `RetryableFailure` | `RetryableFailure/TransferFailed` |
| contrainte ou contenu invalide | `Rejected` | `Rejected/CorruptedEvent` |

Toutes les issues sont fermées.
