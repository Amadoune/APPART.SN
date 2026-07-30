# Phase 5.2A — Quality Baseline

## Campagnes finales

| Campagne | Résultat terminal |
|---|---|
| Unit + Feature + Architecture Public Integration | 8 tests, 67 assertions, PASS |
| PostgreSQL Public Integration end-to-end | 1 test, 6 assertions, PASS |
| Architecture complète | 626 tests, 48 114 assertions, PASS |
| Suite applicative | 2 816 tests, 56 333 assertions, PASS |
| Build Vite production | PASS |
| npm audit | 0 vulnérabilité, PASS |
| PHPStan | 0 erreur, PASS |
| Pint | PASS |
| `git diff --check` | PASS |

## Preuves intermédiaires conservées

- Implementation : Architecture 611/46 908, applicative 2 786/55 068,
  PostgreSQL 5/26 ;
- Persistence : Architecture 613/47 421, applicative 2 788/55 581,
  PostgreSQL 5/26 ;
- Runtime : Architecture 616/47 597, applicative 2 793/55 774,
  PostgreSQL 5/26 ;
- HTTP : Architecture 619/47 752, applicative 2 800/55 945,
  PostgreSQL 5/26 ;
- Operations : ciblée 8/49, Architecture 622/47 906,
  applicative 2 808/56 110, PostgreSQL 2/16.

Les campagnes PostgreSQL retenues sont les campagnes propriétaires ciblées,
conformément aux décisions d’autorité de la tranche. Aucun résultat global non
terminal n’est revendiqué.
