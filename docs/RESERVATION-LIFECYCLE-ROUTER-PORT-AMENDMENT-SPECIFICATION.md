# Reservation Lifecycle Router Port Amendment Specification

## Signature certifiable

```php
public function route(
    ReservationLifecycleTransportEnvelope $envelope,
): ReservationLifecycleRoutingResult;
```

Le port conserve un seul paramètre : l'enveloppe V1 certifiée en 4.3F. Il ne reçoit ni PDO, ni transaction, ni horloge, ni contexte Laravel.

Le résultat est obligatoire et fermé. Une future implémentation ne pourra donc ni masquer une disposition, ni assimiler silencieusement un échec à un succès.

4.3F-R1 ne fournit aucune classe implémentant ce port, aucun binding et aucun appel à `route()`. Le port représente toujours uniquement une frontière applicative.
