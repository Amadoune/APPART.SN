# Administration Console Event Foundation

## Périmètre

La Foundation matérialise exclusivement trois catalogues Event V1 : Operator, Queue et Audit. Chaque catalogue contient un Event V1, une Factory, un Payload, un Type et un Status.

Les Factories dépendent respectivement et exclusivement de `AdministrationOperatorReaderV1`, `AdministrationQueueReaderV1` et `AdministrationAuditReaderV1`. Chaque invocation lit exactement un résultat public et produit exactement un événement.

## Forme des événements

Les types fermés sont :

- `administration_console.operator.observed.v1` ;
- `administration_console.queue.observed.v1` ;
- `administration_console.audit.observed.v1`.

Chaque payload canonique contient exclusivement `status` et `observedAt`. `observedAt` provient de `AdministrationObservedAt::canonical()` et reste en UTC à la microseconde.

La clé sujet sert uniquement à appeler le Reader et ne franchit pas la frontière Event. Aucune PII, révision ou métadonnée interne n'est exposée.

Aucun Provider, Runtime, Runtime Read, HTTP, Delivery, Outbox, Transport, Routing, Consumer, PostgreSQL, SQL ou migration n'est créé.
