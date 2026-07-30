# Media Item Lifecycle Outbox Catalog Extension Specification

| Event type | Owner | Aggregate | Version | Payload |
|---|---|---|---|---|
| `media.item.lifecycle.removed` | `Media` | `MediaItemLifecycle` | V1 | `MediaItemLifecycleDeliveryPayload` |
| `media.item.lifecycle.archived` | `Media` | `MediaItemLifecycle` | V1 | `MediaItemLifecycleDeliveryPayload` |

Chaque couple type/version est unique. Le catalogue rejette toute combinaison d'owner, d'aggregate, de version ou de payload différente.
