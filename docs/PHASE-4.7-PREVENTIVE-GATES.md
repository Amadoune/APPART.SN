# Phase 4.7 — Preventive Certification Gates

## Gates transverses

- Toute incompatibilité avec un contrat gelé impose un amendement versionné préalable.
- Chaque résultat est fermé ; aucune exception technique ne traverse une frontière applicative.
- Aucun `default`, fallback, Fake de production, Null Object, horloge ou identité implicite.
- Chaque sprint prouve Unit/contractuel, PostgreSQL si concerné, Architecture, non-régression et qualité.

## Gates par fondation

| Fondation | GO obligatoire | NO GO immédiat |
|---|---|---|
| contexte décisionnel | propriétaire, preuves et politique four-eyes non ambigus | décision reconstruite depuis l'Aggregate |
| Workflow | matrice exhaustive et pure avec contexte certifié | lecture externe ou effet d'audit |
| persistance | coexistence historique, append-only, idempotence, concurrence | modification directe du stockage historique |
| orchestration | contexte explicite, inspection exacte, huit issues fermées | rappel du workflow sur rejeu |
| Event | bijection transitions/faits et confidentialité | motif ou contenu d'audit exposé |
| Transport | opacité byte-for-byte, identités séparées, résultat fermé | acquittement silencieux |
| Routing | Inbox durable avant `Routed` | routeur sans preuve durable |
| Consumption | matrice ack/retry/quarantaine certifiée | décision laissée au Consumer |
| Outbox | owner audité et isolation démontrée | migration ou owner dupliqué |
| Atomicité | journal, contexte et Outbox dans une transaction | compensation ou commit partiel |
| HTTP | validation transport et délégation unique | logique métier ou transaction HTTP |
