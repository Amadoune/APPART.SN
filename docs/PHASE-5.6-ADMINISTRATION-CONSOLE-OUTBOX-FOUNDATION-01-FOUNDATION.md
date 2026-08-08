# Administration Console Outbox Foundation

## Périmètre

La Foundation matérialise une Outbox owner-scoped `AdministrationConsole` composée des ports Application `AdministrationConsoleOutboxWriter` et `AdministrationConsoleOutboxReader`, d'une Policy, d'un Result, d'un Status et de l'unique Repository `PostgreSqlAdministrationConsoleOutboxRepository`.

Les seules sources sont `AdministrationOperatorDeliveryV1`, `AdministrationQueueDeliveryV1` et `AdministrationAuditDeliveryV1`. Une Delivery produit exactement un message Outbox.

## Persistance

La migration additive 083 crée `administration_console.outbox_messages`, journal append-only identifié par un SHA-256 déterministe. Le message canonique contient, dans cet ordre, le type Event, le statut et `observedAt`. Un checksum SHA-256 distinct est préfixé par la version de politique.

Le Repository assure `Applied`, `AlreadyApplied`, `DivergentMessage` et `DependencyUnavailable`, l'ordre `(created_at, message_id)`, un retry strictement inférieur à 10, `ON CONFLICT`, verrouillage `FOR UPDATE`, savepoints locaux et préservation du rollback externe.

La lecture reconstruit les trois Deliveries concrètes et normalise `observedAt` en UTC canonique à la microseconde.

Aucun Provider, Runtime, Runtime Read, HTTP, Event supplémentaire, Transport, Routing ou Consumer n'est créé.
