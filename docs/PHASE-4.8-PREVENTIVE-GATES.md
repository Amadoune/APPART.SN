# Phase 4.8 — Place Lifecycle Preventive Certification Gates

## Gates transverses

- Les baselines certifiées restent au minimum : 2 463 tests, 47 544
  assertions; PostgreSQL 521 tests, 2 201 assertions; Architecture 500 tests,
  40 309 assertions; Runtime Health Healthy, 50 capacités.
- Toute baisse, fluctuation non expliquée ou incompatibilité avec un contrat
  gelé impose **NO GO**.
- Toute évolution nécessaire d'une Phase 2, 3 ou 4.1 à 4.7 exige un amendement
  versionné certifié avant reprise.
- Aucun fallback, `default`, Fake de production, Null Object, horloge, acteur
  ou identité implicite.
- Chaque frontière expose des résultats fermés et ne laisse traverser aucune
  exception technique.
- Chaque jalon dispose d'un GO indépendant; aucun jalon R ne peut être absorbé.

## Gates par fondation

| Gate | GO obligatoire | NO GO immédiat |
|---|---|---|
| Discovery | owner, périmètre, terminalité, matrice, risques et roadmap approuvés | ambiguïté sur `Merged` ou fusion depuis `Disabled` |
| R1 Merge Context | cible, version, état, type, pays, acteur, instant et intention explicites | lecture de cible dans le Workflow |
| Workflow | matrice exhaustive, pure et déterministe | renommage, hiérarchie ou projection dans la décision |
| Persistence | append-only, idempotence, concurrence source/cible, rollback total | mutation partielle ou chaîne de fusion |
| Runtime Composition | bindings paresseux, alias unique, health additif | résolution eager ou régression des 50 capacités |
| Orchestration | contexte exact et rejeu par inspection durable | reconstruction depuis l'état courant |
| Event Contract | bijection transitions/faits et payload minimal | nom, coordonnées, alias ou justification libre exposés |
| Transport | enveloppe versionnée, payload opaque, checksum et résultats fermés | sérialisation métier par le routeur |
| Routing | Inbox durable avant acquittement | `Routed` sans preuve durable |
| Consumption | matrice ack/retry/quarantaine certifiée | décision implicite du Consumer |
| R2 Outbox Owner | owner Geography audité et isolé | owner supposé ou migration dupliquée |
| Outbox Compatibility | catalogue, mapper et restauration compatibles | modification destructive d'un contrat partagé |
| Atomicity | transition, contexte et Outbox dans une transaction | compensation ou commit partiel |
| HTTP | validation stricte et délégation atomique unique | logique métier, lot ou transaction dans l'adapter |
| Final | toutes certifications GO et baseline égale ou supérieure | dette, waiver implicite ou anomalie ouverte |
