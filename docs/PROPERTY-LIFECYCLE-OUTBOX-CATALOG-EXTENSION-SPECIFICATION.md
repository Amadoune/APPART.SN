# Property Lifecycle Outbox Catalog Extension Specification

| Famille | Nombre | Source normative | Payload |
|---|---:|---|---|
| Reconstruction historique | 5 | liste technique certifiée | payload historique dédié |
| Listing Publication | 15 | `ListingPublicationEventType::cases()` | `ListingPublicationDeliveryPayload` |
| Property Lifecycle | 7 | `PropertyLifecycleEventType::cases()` | `PropertyLifecycleDeliveryPayload` |

Le catalogue contient 27 types en version 1. Les entrées Property exigent `RealEstateCatalog`, `Property` et l'identité portée par `propertyId`. Un type absent est `UnsupportedType`; une version différente est `UnsupportedVersion`.
