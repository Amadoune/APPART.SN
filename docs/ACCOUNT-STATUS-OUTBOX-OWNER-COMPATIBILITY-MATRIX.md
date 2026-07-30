# Account Status Outbox Owner — Compatibility Matrix

| Dimension | État | Décision / gate |
|---|---|---|
| module owner `IdentityAccess` | COMPATIBLE | valeur distincte des dix owners existants |
| schéma owner `identity_access` | COMPATIBLE | schéma existant, tables Outbox absentes |
| aggregate type `AccountStatus` | COMPATIBLE | distinct des agrégats historiques |
| namespace `account.status.*` | COMPATIBLE | deux types uniques |
| payload générique | COMPATIBLE | `AccountStatusDeliveryPayload` implémente `PublicProjectionDeliveryPayload` |
| checksum | COMPATIBLE | SHA-256 déterministe exposé par `checksum()` |
| catalogue Delivery | GATE J3 | les deux types Account Status ne sont pas encore enregistrés |
| mapper Outbox | GATE J4 | aucune restauration `AccountStatusDeliveryPayload::restore()` |
| Consumer générique | GATE J5 BLOQUANT | le Consumer 4.9I reçoit message + destination, pas `PublicProjectionDeliveryMessage` seul |
| Writer / Reader | RÉUTILISABLE | aucune spécialisation requise |
| Worker | CONDITIONNEL | réutilisable après résolution normative de J5 |
| migration owner | ABSENTE | 043 réservée; interdite pendant R1 |

La compatibilité du payload ne suffit pas à autoriser 4.9J. Les gates J3 à J5
doivent être satisfaits sans modification silencieuse des contrats 4.9F à
4.9I.
