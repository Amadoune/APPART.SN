# Phase 5.1C — PostgreSQL Certification Specification

## 1. Environnement

- PostgreSQL 18.x réel ;
- PDO PostgreSQL ;
- base de test dédiée ;
- migrations 001–043 appliquées avant 044–051 ;
- aucun fallback SQLite.

## 2. Suites obligatoires

| Suite | Preuves |
|---|---|
| Schema | tables, CHECK, uniques, indexes, ownership |
| Migration | forward/down/reapply isolés |
| Mapper | snapshot → row → snapshot exact |
| Repository | add/read/save/append selon owner |
| Idempotence | intent identique et divergence checksum |
| Optimistic lock | stale expectedVersion refusée |
| Constraints | invalid state/time/version/secret rejected |
| Transaction | outer transaction participation + rollback |
| Concurrency | scénarios de la stratégie |
| Frozen | 041–043 structure et comportement inchangés |
| Privacy | aucune colonne/token/PII interdite en clair |

## 3. Comptes attendus

Le nombre de tests n'est pas fixé avant implémentation, mais aucun owner ne
peut être certifié avec moins de :

- un round-trip ;
- un mapping corruption ;
- un idempotency replay/conflict ;
- un optimistic locking conflict ;
- un rollback ;
- un concurrency test réel ;
- un migration/down/reapply.

Claims et Sessions exigent au moins deux tests de concurrence chacun.

## 4. Commandes

```bash
composer test:postgresql
composer test:architecture
composer quality
git diff --check
```

La campagne ciblée 5.1C est exécutée avant la campagne complète.

## 5. Verdicts

PASS signifie zéro failure/error/risky et aucun skip non justifié. Timeout ou
instance indisponible = NON CONCLUSIVE, jamais PASS.

## 6. Exécution ciblée enregistrée

Le 2026-07-26, sur PostgreSQL 18.x réel :

```text
PostgreSqlIdentityAccessCompletionPersistenceTest
6 tests, 25 assertions — PASS
```

Preuves obtenues : round-trip des huit owners, optimistic locking,
idempotence/checksum divergent, rollback de transaction appelante, checkpoint
session monotone, course réelle à deux processus, zéro FK cross-domain et
rollback/reapply local de 051 préservant 047.

La campagne globale du dépôt reste une preuve distincte de non-régression.
