# Administration Console Delivery Foundation

## Périmètre

La Foundation matérialise exclusivement trois catalogues Delivery V1 : Operator, Queue et Audit. Chaque catalogue contient une Delivery V1, une Factory, un Payload, un Status et un Result.

Les seules sources autorisées sont respectivement `AdministrationOperatorEventV1`, `AdministrationQueueEventV1` et `AdministrationAuditEventV1`. Les Factories sont sans dépendance injectée et acceptent uniquement leur Event V1.

## Propagation

Chaque Event V1 produit exactement une Delivery V1. Le type Event est conservé comme propriété de la Delivery. Le payload Delivery contient exclusivement le statut homonyme et `observedAt` inchangé.

Cette structure conserve le type sans l'ajouter au payload, lequel reste strictement limité à `status` et `observedAt`.

Aucun Provider, Runtime, Runtime Read, HTTP, Outbox, Transport, Routing, Consumer, PostgreSQL, SQL ou migration n'est créé.
