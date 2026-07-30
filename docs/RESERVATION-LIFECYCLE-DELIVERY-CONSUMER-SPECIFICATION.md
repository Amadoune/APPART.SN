# Reservation Lifecycle Delivery Consumer Specification

La séquence normative est fermée :

1. vérifier que le payload est `ReservationLifecycleDeliveryPayload` ;
2. restaurer strictement le payload canonique ;
3. vérifier type, version, owner, aggregate, identité, version et index Delivery ;
4. restaurer l'enveloppe V1 via le contrat certifié ;
5. appeler exactement une fois `ReservationLifecycleEventRouterPort::route()` ;
6. remettre exactement son résultat à `ReservationLifecycleDeliveryConsumptionPolicy`.

Toute divergence avant routage produit `DivergentPayload` et n'appelle jamais le routeur. La conversion après routage reste exclusivement propriétaire de la politique 4.3G-R1.
