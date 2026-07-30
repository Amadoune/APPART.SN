# Phase 5.2A — Authoring Operations Certification

## Décision d’autorité

**GO CERTIFIÉ — FERMÉ.**

## Preuves terminales

| Campagne | Résultat |
|---|---|
| Unit + Feature + Architecture ciblés | 8 tests, 49 assertions, PASS |
| PostgreSQL 18.x Operations ciblée | 2 tests, 16 assertions, PASS |
| Architecture complète | 622 tests, 47 906 assertions, PASS |
| Suite applicative | 2 808 tests, 56 110 assertions, PASS |
| PHPStan | 0 erreur, PASS |
| Pint | PASS |
| `git diff --check` | PASS |

## Scénarios certifiables

- checksum déterministe et validation des commandes ;
- bindings lazy et singleton sans remplacement du HTTP certifié ;
- création complète appliquée puis rejouée ;
- ownership et portfolio owner-scoped ;
- rollback d’une transaction englobante sur Listing, draft, ownership et
  portfolio ;
- handoff exact vers l’action publique F-01 `Submit` ;
- traduction fermée des conflits et indisponibilités ;
- absence de dépendance Application vers Infrastructure ;
- absence de dépendance directe inter-module ;
- Runtime Health maintenu à 58 capacités.

La campagne PostgreSQL retenue est la campagne ciblée Operations. Aucune
campagne PostgreSQL globale n’est revendiquée.
