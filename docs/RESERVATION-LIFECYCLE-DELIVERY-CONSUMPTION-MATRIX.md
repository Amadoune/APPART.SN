# Reservation Lifecycle Delivery Consumption Matrix

| Résultat de routage | Résultat de consommation | Acquittement | Retry | Quarantaine | Justification |
|---|---|---:|---:|---:|---|
| `Stored` | `Consumed` | oui | non | non | première persistance durable réussie |
| `AlreadyStored` | `AlreadyConsumed` | oui | non | non | rejeu strictement idempotent déjà durable |
| `CorruptedEnvelope` | `DivergentPayload` | non | non | oui | divergence permanente des données transportées |
| `PersistenceCorrupted` | `RetryableFailure` | non | oui | après épuisement de la politique | l'infrastructure n'a pas produit de disposition fiable |

Les effets indiqués sont ceux du `PublicProjectionDeliveryWorker` certifié. `Consumed` et `AlreadyConsumed` appellent tous deux `markDelivered`. `DivergentPayload` produit une quarantaine immédiate. `RetryableFailure` délègue à la politique de retry existante.

La matrice est exhaustive sur `ReservationLifecycleRoutingStatus`; aucune décision ne restera au futur Consumer 4.3H.
