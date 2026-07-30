# Reservation Lifecycle Routing Outcome Contract Amendment Analysis

## Cause

Le port 4.3F certifié acceptait une `ReservationLifecycleTransportEnvelope` mais retournait `void`. Cette signature ne pouvait pas exprimer les quatre issues fermées exigées par le routage durable 4.3G. Une implémentation concrète aurait dû ignorer son résultat, contourner le port ou exposer des exceptions : ces trois solutions sont interdites.

## Amendement

Le sprint 4.3F-R1 ajoute exclusivement :

- `ReservationLifecycleRoutingStatus`, enum fermé de quatre valeurs ;
- `ReservationLifecycleRoutingResult`, résultat final, readonly et entièrement typé.

Le paramètre du port est inchangé. Son retour devient `ReservationLifecycleRoutingResult`. Aucun comportement, aucune implémentation et aucune dépendance technique ne sont ajoutés.

## Compatibilité

`ReservationLifecycleDeliveryPayload`, `ReservationLifecycleDeliveryMetadata`, `ReservationLifecycleTransportEnvelope` et `ReservationLifecycleTransportSerializer` restent byte-for-byte inchangés. Les règles de `canonicalEvent`, `messageId`, `businessEventId`, checksum et transport V1 sont identiques.

Le changement est volontairement incompatible uniquement pour une future implémentation du port. Aucune implémentation n'existait lors de l'amendement; aucune migration d'adaptateur n'est donc nécessaire.
