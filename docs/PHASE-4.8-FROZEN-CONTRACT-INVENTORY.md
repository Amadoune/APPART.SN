# Phase 4.8 — Inventaire des contrats gelés

## Métier et décision

- `PlaceMergeContextV1` et ses Value Objects;
- `PlaceLifecycleState`, `PlaceLifecycleAction`, `PlaceLifecycleDecision`;
- `PlaceLifecycleWorkflow` et sa matrice fermée;
- `PlaceMergeContextInspector` et `PlaceMergeReplayClassifier`;
- `PlaceLifecycleOrchestrator`, requête, résultat et statuts.

## Persistance

- `PlaceLifecycleWorkflowStore`;
- journal append-only et mapping de contexte;
- `PostgreSqlPlaceLifecycleWorkflowStore`;
- `PostgreSqlPlaceMergeContextInspector`;
- `DeterministicPlaceMergeReplayClassifier`.

## Événement et livraison

- `PlaceLifecycleEventV1` et les trois types certifiés;
- `PlaceLifecycleDeliveryPayload`;
- `PlaceLifecycleTransportEnvelope`, checksum, metadata et serializer;
- `PlaceLifecycleEventRouter` et Inbox durable;
- `PlaceLifecycleDeliveryConsumer` et sa Policy;
- adaptations génériques R2/R3.

## Outbox et atomicité

- owner `Geography ↔ geography`;
- catalogue, mapper, Writer, Reader et Worker PublicProjection génériques;
- `PlaceLifecycleAtomicTransaction`;
- `PlaceLifecycleAtomicEventRequest`;
- `PlaceLifecycleAtomicEventOrchestrator`;
- transaction générique `PostgreSqlAggregateOutboxTransaction`.

## HTTP

- `PlaceLifecycleTransitionRequest`;
- `PlaceLifecycleHttpController`;
- `PlaceLifecycleHttpResultMapper`;
- endpoint `POST /api/place-lifecycles/{placeId}/transitions`.

Toute modification directe d'un élément inventorié est interdite sans
amendement versionné préalable.
