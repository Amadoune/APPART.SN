# Account Status Outbox Owner — Collision Risk Matrix

| Risque | Verdict | Mesure normative |
|---|---|---|
| collision module | ABSENTE | réserver `IdentityAccess` |
| collision schéma | MAÎTRISÉE | ajouter seulement les quatre tables conventionnelles au schéma existant |
| collision aggregate type | ABSENTE | réserver `AccountStatus` |
| collision event type | ABSENTE | réserver `account.status.suspended/reactivated` |
| confusion eventId/messageId/idempotency key | OUVERTE | conserver les trois identités distinctes |
| duplication Writer/Reader/Mapper/Worker | INTERDITE | réutiliser les composants génériques |
| double Consumer | OUVERTE | résoudre J5 avant toute registration Worker |
| modification 041/042 | INTERDITE | migration 043 strictement additive |
| double propriété transactionnelle | OUVERTE | décision réservée à l'intégration atomique future |
