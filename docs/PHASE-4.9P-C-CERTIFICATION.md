# Phase 4.9P-C — Historical Account PostgreSQL Persistence Certification

## Livrables

- `PostgreSqlAccountRepository`;
- migration 042 additive;
- rollback 042;
- matrice SQL / Snapshot V1;
- analyse transactionnelle;
- tests PostgreSQL et Architecture.

## Matrice GO

| Critère | Résultat |
|---|---|
| AccountRegistry intégralement implémenté | SATISFAIT |
| reconstruction fidèle | SATISFAIT |
| atomicité racine/enfants | SATISFAIT |
| transaction externe respectée | SATISFAIT |
| concurrence optimiste | SATISFAIT |
| unicités PostgreSQL | SATISFAIT |
| historiques et ordinals conservés | SATISFAIT |
| secrets via frontière V1 | SATISFAIT |
| corruption distincte de Missing | SATISFAIT |
| journal 041 inchangé | SATISFAIT |
| aucun Runtime | SATISFAIT |

## Validations finales

```text
PostgreSQL Repository
9 tests
35 assertions
PASS

Architecture ciblée
5 tests
39 assertions
PASS

PostgreSQL complète
555 tests
2 355 assertions
PASS

Architecture complète
560 tests
43 426 assertions
PASS

Suite complète
2 651 tests
51 138 assertions
PASS

Pint
PASS

PHPStan
0 erreur
```

Le rollback partagé est validé dans l'ordre inverse des migrations :

```text
042 down
→ 041 down
→ 041 up
→ 042 up
```

La migration 041 reste inchangée.

## Frontière

Aucun ServiceProvider, binding Laravel, Runtime Health, Event, Outbox, HTTP ou
import legacy n'est introduit. Le journal 041 demeure l'unique autorité du
lifecycle Account Status.

## Verdict certifié

```text
4.9P-C
→ GO CERTIFIÉ
→ FERMÉ

4.9P-D
→ OUVERT

4.9D
→ RESTE SUSPENDU
```
