# Reservation Lifecycle Worker Registration Matrix

| Groupe | Nombre | Consumer |
|---|---:|---|
| messages historiques | 5 | Consumer générique existant |
| Listing Publication | 15 | Consumer Listing existant |
| Property Lifecycle | 7 | Consumer Property existant |
| Reservation Lifecycle | 11 | `ReservationLifecycleDeliveryConsumer` |
| Total | 38 | registre générique unique |

Chaque inscription Reservation utilise la version 1 et exactement une valeur de `ReservationLifecycleEventType`. Les 38 couples type/version sont uniques. Aucun Worker, registre ou mécanisme d'exécution parallèle n'est introduit.
