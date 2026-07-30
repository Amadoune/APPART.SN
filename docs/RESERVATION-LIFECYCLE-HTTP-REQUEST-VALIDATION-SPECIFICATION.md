# Reservation Lifecycle HTTP Request Validation Specification

| Champ | Validation de transport |
|---|---|
| `reservationId` | UUID imposé par la route |
| `action` | une des sept actions exécutables, `unknown` exclue |
| `expectedVersion` | entier strictement positif |
| `occurredAt` | UTC canonique avec six décimales |
| `recordedAt` | UTC canonique avec six décimales, supérieur ou égal à `occurredAt` |

Aucun état, aucune transition et aucune règle métier ne sont validés dans HTTP. Une requête invalide ne résout aucune décision et n'appelle pas l'intégrateur.
