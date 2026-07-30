# Reservation Lifecycle Event Payload V1 Specification

## Champs normatifs

| Ordre | Champ | Type | Règle |
|---:|---|---|---|
| 1 | `eventId` | string | `reservation-lifecycle-` suivi d'un SHA-256 hexadécimal |
| 2 | `aggregateType` | string | valeur fixe `ReservationLifecycle` |
| 3 | `reservationId` | UUID canonique | identité applicative existante |
| 4 | `transition` | string | `previousState>action>currentState` |
| 5 | `previousState` | enum | état source certifié |
| 6 | `currentState` | enum | état cible certifié |
| 7 | `action` | enum | action certifiée |
| 8 | `version` | entier | valeur fixe `1` |
| 9 | `occurredVersion` | entier positif | version du journal résultant de la transition |

Le payload est immuable. Il rejette une version différente de V1, une `occurredVersion` non positive ou une représentation de transition incohérente.

## Identité

L'identité est dérivée, dans cet ordre, de `aggregateType`, `eventType`, `payloadVersion`, `reservationId`, `transition`, `previousState`, `currentState`, `action`, `version` et `occurredVersion`, séparés par `|`, puis condensés par SHA-256. Les mêmes données produisent donc la même identité; toute donnée normative différente produit une identité différente.

Les métadonnées contractuelles associent exclusivement `eventType` et `payloadVersion`. Aucun instant implicite n'appartient au contrat 4.3E.
