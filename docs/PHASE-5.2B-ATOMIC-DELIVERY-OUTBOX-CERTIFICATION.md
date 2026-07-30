# Phase 5.2B — Atomic Delivery / Outbox Integration Certification

## Décision d’autorité

**GO CERTIFIÉ — FERMÉE.**

## Preuves propres au périmètre

| Campagne | Résultat |
|---|---|
| Unit + Feature + Architecture ciblés | 3 tests, 24 assertions, PASS |
| Architecture complète | 636 tests, 49 668 assertions, PASS |
| Unit complète | 1 930 tests, 6 806 assertions, PASS |
| PostgreSQL Outbox ciblé | 4 tests, 24 assertions, PASS |
| PHPStan | 0 erreur, PASS |
| Pint | PASS |
| `git diff --check` | PASS |

## Éléments certifiés

- Outbox propriétaire Media Ingestion ;
- résultats fermés `Applied`, `AlreadyApplied`, `DivergentMessage` ;
- convergence déterministe sur `message_id` et `event_id` ;
- persistance atomique du message et de ses destinations ;
- claims concurrents, leases, retry, reprise et quarantaine ;
- participation transactionnelle sûre par savepoint ;
- migration additive 060 avec `message_id varchar(83)`.

La migration 060 est certifiée mais ne devient gelée qu’au GO FINAL de la
tranche 5.2B.

## Réserve globale indépendante

La campagne PostgreSQL complète n’est pas verte uniquement à cause de
`PostgreSqlReservationLifecycleAtomicEventIntegrationTest`. L’écart
`AlreadyApplied` / `VersionConflict` provient d’une ambiguïté historique
d’ordonnancement entre lecture, contrôle anticipé de version et verrouillage
dans `append()`.

Cette réserve est :

- hors périmètre 5.2B ;
- une non-régression Media Ingestion ;
- indépendante de la certification de cette Foundation ;
- interdite de correction sans amendement versionné Reservation Lifecycle.

Final Certification & Freeze est désormais le seul jalon ouvert par décision
d’autorité.
