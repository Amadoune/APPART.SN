# Place Lifecycle Delivery Consumption — Spécification

## Matrice fermée

| Résultat Routing | Décision Consumption |
|---|---|
| `Routed` | `Acknowledged` |
| `Deferred` | `Retry` |
| `RetryableFailure` | `Retry` |
| `Rejected` | `Quarantined` |

Le consommateur délègue exclusivement au routeur 4.8H puis applique cette
politique. Toute exception technique devient `Retry`.

## Composition Runtime

Bindings additifs et paresseux :

- serializer Transport;
- repository Inbox et alias du port;
- routeur durable et alias du port 4.8G;
- politique de consommation;
- consommateur logique.

Runtime Health passe de 52 à 55 capacités avec :

- `place_lifecycle_inbox_store`;
- `place_lifecycle_event_router`;
- `place_lifecycle_delivery_consumer`.

## Frontière

Aucun Worker, Outbox, HTTP ou appel distant n'est créé. La consommation
n'interprète aucune règle métier et ne modifie aucune fondation certifiée.
