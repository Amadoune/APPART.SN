# Property Lifecycle Consumer Compatibility Specification

`PropertyLifecycleEventDeliveryConsumer` accepte uniquement `PropertyLifecycleDeliveryPayload`. Il restaure l'événement, compare ses données aux champs techniques, puis invoque exactement une fois `PropertyLifecycleEventRouter`.

| Résultat routeur | Résultat Delivery | Acquittement |
|---|---|---|
| `Routed` | `Consumed` | oui |
| `Deferred` | `BlockedBySourceReadiness` | non |
| `RetryableFailure` | `RetryableFailure` | non |
| `Rejected` | `PermanentFailure` | non |

Un payload divergent produit `DivergentPayload` sans routage. Le Consumer ne dépend ni du workflow, ni de HTTP, ni d'une Projection.
