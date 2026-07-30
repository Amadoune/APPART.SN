# Phase 5.3H — Outbox Foundation Certification

## Décision proposée

**GO PROPOSÉ**

## Périmètre certifié

La véritable Outbox propriétaire de `ModerationReports` est complète :

- messages immuables transportant exclusivement `ModerationEventV1` ;
- destinations owner-local issues du routeur fermé certifié en 5.3G ;
- append, lecture, claim, release, retry, delivery, replay et quarantaine ;
- idempotence `Stored`, `AlreadyStored` et `DivergentMessage` ;
- claims concurrents avec `FOR UPDATE SKIP LOCKED` ;
- lease et reprise des claims expirés ;
- advisory lock transactionnel sur `messageId` ;
- intégration atomique mutation métier + append via
  `ModerationAtomicOperationV1`.

## Migration

La migration additive `067_moderation_event_outbox` crée exclusivement :

- `moderation_reports.outbox_messages` ;
- `moderation_reports.outbox_deliveries` ;
- l'index local de claim.

Elle ne contient aucune FK cross-domain, cascade ou trigger. Son rollback est
complet. Les migrations 063 à 066 restent inchangées.

## Preuves ciblées

| Campagne | Résultat |
|---|---|
| Unit + Architecture ciblés | 2 tests, 17 assertions — PASS |
| PostgreSQL Outbox ciblé | 5 tests, 34 assertions — PASS |

Les scénarios PostgreSQL démontrent :

- append et répétition idempotente ;
- divergence sans duplication ;
- claim, release, retry et expiration ;
- delivery, replay et quarantaine ;
- rollback métier en cas d'append divergent ;
- transaction englobante et savepoint ;
- rollback externe des écritures métier et Outbox ;
- concurrence : un `Stored`, un `AlreadyStored`.

## Campagnes terminales

| Campagne | Résultat |
|---|---|
| Architecture complète | 665 tests, 52 605 assertions — PASS |
| Unit complète | 1 953 tests, 6 949 assertions — PASS |
| PostgreSQL complète terminale | 640 tests, 2 842 assertions — PASS |
| PHPStan | 0 erreur — PASS |
| Pint, périmètre 5.3H | PASS |
| git diff --check | PASS |

Une première campagne PostgreSQL a rencontré un aléa concurrent historique
Listing Lifecycle. Une seconde a reproduit la réserve historique Reservation
Lifecycle. Les deux tests isolés ont été exécutés avec succès sans modification
de ces domaines. Seule la troisième campagne complète, terminale et entièrement
verte, est retenue comme preuve normative.

## Frontières préservées

Aucune consommation de IAM, Listing, Media, Account, Professional ou
Administration Audit n'est introduite. Les six amendements 5.3C restent
identifiés, non ouverts et non consommés.

Aucun HTTP, Controller, Route, Reader externe, Gateway externe, handoff ou
consumer externe n'est créé. Runtime Health, 5.3F, 5.3G, les contrats V1 et les
migrations 063 à 066 restent inchangés.

## Conclusion

L'Outbox est owner-local, append-only, transactionnelle, idempotente et sûre
sous concurrence. L'append est atomique avec la mutation métier par la frontière
certifiée `ModerationAtomicOperationV1`.

**Phase 5.3H — Outbox Foundation → GO PROPOSÉ.**

La Phase 5.3I n'est pas ouverte automatiquement.
