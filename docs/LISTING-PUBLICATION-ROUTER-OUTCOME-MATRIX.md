# Listing Publication Router Outcome Matrix

| Résultat destination | Résultat routeur | Acquittement autorisé |
|---|---|---|
| `Stored` | `Routed` | Oui |
| `AlreadyStored` strictement identique | `Routed` | Oui |
| `Unavailable` | `Deferred / RouteUnavailable` | Non |
| `RetryableFailure` | `RetryableFailure / TransferFailed` | Non |
| `Rejected` | `Rejected / CorruptedEvent` | Non |

Le mapping est fermé et ne contient aucune branche implicite.
