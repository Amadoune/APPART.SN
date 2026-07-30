# Reservation Lifecycle Routing Runtime Binding Specification

## Graphe de production

```text
ReservationLifecycleEventRouterPort
  -> DeterministicReservationLifecycleEventRouter (singleton)
     -> ReservationLifecycleInboxStore
        -> PostgreSqlReservationLifecycleInboxRepository (singleton)
           -> PDO PostgreSQL Runtime existant
           -> ReservationLifecycleTransportSerializer (singleton)
```

## Bindings

| Contrat ou classe | Enregistrement | Instance partagée |
|---|---|---:|
| `ReservationLifecycleTransportSerializer` | singleton | oui |
| `PostgreSqlReservationLifecycleInboxRepository` | singleton | oui |
| `ReservationLifecycleInboxStore` | alias du repository | oui |
| `DeterministicReservationLifecycleEventRouter` | singleton | oui |
| `ReservationLifecycleEventRouterPort` | alias du routeur | oui |

Aucun Fake, Null Object, fallback ou Provider parallèle n'est introduit. La constructibilité ne requiert ni migration exécutée ni accès à une table au bootstrap.
