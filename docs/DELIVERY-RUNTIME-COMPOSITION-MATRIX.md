# Delivery Runtime Composition — matrice

| Contrat ou composant | Implémentation de production | Cycle de vie |
|---|---|---|
| `PublicProjectionOutboxReader` | `PostgreSqlPublicProjectionOutboxReader` | binding |
| `PublicProjectionOutboxWriter` | `PostgreSqlPublicProjectionOutboxWriter` | binding |
| `PublicProjectionOutboxClaimManager` | `PostgreSqlPublicProjectionOutboxClaimManager` | binding |
| `PublicProjectionOutboxRetryPolicy` | `PublicProjectionDeterministicRetryPolicy` | singleton |
| `PublicProjectionDeliveryClock` | `SystemPublicProjectionDeliveryClock` | binding |
| `PublicProjectionOutboxConsumerId` | configuration typée | singleton |
| `PublicProjectionDeliveryWorkerId` | configuration typée | singleton |
| `PublicProjectionDeliveryConsumerRegistry` | cinq inscriptions explicites | singleton |
| `PublicProjectionDeliveryWorker` | Worker certifié injecté | singleton |

Aucun Fake, Null Object ou fallback n'appartient au graphe.
