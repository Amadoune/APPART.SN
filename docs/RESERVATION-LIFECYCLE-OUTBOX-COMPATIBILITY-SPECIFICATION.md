# Reservation Lifecycle Outbox Compatibility Specification

Le catalogue contient les onze valeurs de `ReservationLifecycleEventType`, toutes avec :

- source module : `ReservationLifecycle` ;
- aggregate type : `ReservationLifecycle` ;
- payload version : `1` ;
- payload : `ReservationLifecycleDeliveryPayload`.

Le mapper sélectionne `ReservationLifecycleDeliveryPayload::restore()` uniquement lorsque le type appartient au catalogue métier fermé. Il vérifie ensuite le checksum persistant selon le contrat générique existant.

Le registre Worker porte désormais 38 couples type/version uniques : cinq historiques, quinze Listing Publication, sept Property Lifecycle et onze Reservation Lifecycle.
