# Administration Console Owner Reader Foundation

## Périmètre

La Foundation matérialise exclusivement trois Readers owner-scoped entre `AdministrationConsoleOwnerSource` et les contrats publics V1 certifiés :

- `AdministrationOperatorOwnerReader` → `AdministrationOperatorReaderV1` ;
- `AdministrationQueueOwnerReader` → `AdministrationQueueReaderV1` ;
- `AdministrationAuditOwnerReader` → `AdministrationAuditReaderV1`.

`AdministrationConsoleOwnerReaderPolicy` réduit les résultats source vers `AdministrationConsoleOwnerReaderResult`, limité au catalogue fermé `AdministrationConsoleOwnerReaderStatus`. Le contrat `AdministrationConsoleOwnerReaderV1` expose les trois opérations mécaniques de la Policy.

## Composition

`AdministrationConsoleOwnerReaderServiceProvider` enregistre en singletons lazy la Policy et les trois Readers. Il expose un alias de Policy vers `AdministrationConsoleOwnerReaderV1` et exactement un alias public par contrat Reader V1.

## Garanties

La seule source est `AdministrationConsoleOwnerSource`. Les réductions sont exhaustives, mécaniques, bijectives et homonymes, sans fallback, agrégation ou nouvelle décision métier. Les Revision States demeurent internes.

Aucun Runtime, Runtime Read, HTTP, Event, Delivery, Outbox, Transport, Routing, Consumer, PostgreSQL, SQL ou migration n'est introduit.
