# Delivery Matrix

| Élément Event | Élément Delivery | Règle |
|---|---|---|
| EventType | DeliveryV1.type | même instance |
| payload.status | payload.status | valeur homonyme |
| payload.observedAt | payload.observedAt | copie stricte |

Cette propagation s'applique mécaniquement aux quatre statuts de chacune des sept familles, sans transformation, fallback ni agrégation.

