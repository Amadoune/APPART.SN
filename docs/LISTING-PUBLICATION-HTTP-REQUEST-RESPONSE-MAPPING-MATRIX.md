# Listing Publication HTTP Request / Response Mapping Matrix

| Entrée HTTP | Type applicatif | Transformation |
|---|---|---|
| `{listingId}` | `ListingId` | validation UUID et construction stricte |
| `action` | `ListingPublicationAction` | correspondance exacte de la valeur certifiée |
| `expectedVersion` | entier positif | aucune |
| `occurredAt` | `ListingPublicationEventInstant` | construction depuis la valeur UTC canonique exacte |
| `recordedAt` | `ListingPublicationEventInstant` | construction depuis la valeur UTC canonique exacte |

| Résultat applicatif | Corps HTTP |
|---|---|
| succès | `{"status":"applied","diagnostic":null}` ou `already_applied` |
| refus | `{"status":"denied","diagnostic":"<diagnostic workflow exact>"}` |
| conflit | `{"status":"concurrency_conflict","diagnostic":"<diagnostic orchestration exact>"}` |
| échec | `{"status":"persistence_failure","diagnostic":"<diagnostic orchestration exact>"}` |
