# Property Event Inventory

## Événements Domain de l'Aggregate Property

| Événement | Producteur / payload | Version / transaction | Consumer ou transport observé | Promotion ? |
|---|---|---|---|---|
| `PropertyRegistered` | `Property::register`; id, référence, type, surface, rooms, bathrooms, année, adresse, instant | metadata aggregateVersion/eventIndex en mémoire ; Aggregate ajouté transactionnellement | Aucun dispatcher/outbox reliant Authoring à cet événement n'est observé | Non : il est produit après création de l'Aggregate |
| `PropertyUpdated` | `Property::update`; nouveaux détails | Version Aggregate | Aucun consumer de promotion | Non |
| `SurfaceChanged` | `Property::update`; ancienne/nouvelle surface | Même mutation que PropertyUpdated, index distinct | Aucun consumer de promotion | Non |
| `AddressChanged` | `Property::changeAddress`; ancienne/nouvelle adresse | Version Aggregate | Aucun consumer de promotion | Non |
| `PropertyArchived` | `Property::archive`; identité/instant | Version Aggregate | Aucun consumer de promotion | Non |

`PostgreSqlPropertyRepository` persiste le snapshot de l'Aggregate. Aucun mécanisme existant ne consomme `Property::releaseEvents()` pour créer un Property depuis Authoring.

## Événements Property Lifecycle V1

| Type | Transition productrice | Payload V1 | Transport / replay |
|---|---|---|---|
| `property.lifecycle.activated` | draft → activate → active | propertyId, previousState, state, action, lifecycleVersion ; occurredAt/recordedAt | Aggregate+Outbox atomique ; message déterministe |
| `property.lifecycle.archived` | draft/decommissioned → archive → archived | même forme | Outbox puis inbox idempotente par eventId/checksum |
| `property.lifecycle.maintenance_started` | active/unavailable → begin_maintenance | même forme | idem |
| `property.lifecycle.maintenance_completed` | under_maintenance → complete_maintenance | même forme | idem |
| `property.lifecycle.marked_unavailable` | active/under_maintenance → mark_unavailable | même forme | idem |
| `property.lifecycle.availability_restored` | unavailable → restore_availability | même forme | idem |
| `property.lifecycle.decommissioned` | active/maintenance/unavailable → decommission | même forme | idem |

Producteur : `AtomicPropertyLifecycleEventOrchestrator`, owner RealEstateCatalog Lifecycle. Consumer enregistré : `PropertyLifecycleEventDeliveryConsumer`, qui valide puis route vers `PostgreSqlPropertyLifecycleEventInbox`.

Ces événements ne contiennent ni `propertyType`, ni ville, ni quartier, ni référence, ni données nécessaires à `RegisterProperty`. Ils décrivent le changement d'un lifecycle déjà existant et n'ont aucune sémantique de promotion Authoring → Aggregate.

## Public Projection Delivery Property payload

`PublicProjectionDeliveryPropertyPayload` contient uniquement `propertyId`. `CertifiedPublicProjectionSourceLookup` le transforme en demande multi-target pour recalculer les Listings d'un Property existant. Il ne matérialise aucune source Property.

## Conclusion événementielle

**Aucun événement existant ne porte la sémantique de promotion publique recherchée.**
