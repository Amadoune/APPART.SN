# Phase 5.3L — Campagnes terminales finales

## Gates normatives

La baseline de certification impose Architecture, PostgreSQL complet, suite
applicative complète, PHPStan, Pint et `git diff --check`. La suite applicative
est donc traitée comme un gate distinct.

| Campagne | Résultat final | Tests | Assertions | Durée | Exit code |
|---|---|---:|---:|---:|---:|
| Architecture complète | PASS | 697 | 55 965 | 11,550 s | 0 |
| suite applicative complète | PASS | 2 997 | 64 783 | 91,610 s | 0 |
| PostgreSQL complet post-amendements | PASS | 680 | 3 070 | 870,630 s | 0 |
| PHPStan global | PASS — 0 erreur | — | — | 3,6 s | 0 |
| Pint global | PASS | — | — | 5,1 s | 0 |
| `git diff --check` | PASS | — | — | 1,9 s | 0 |

Les campagnes ont été exécutées après l'alignement documentaire, sans
modification du code ou des tests. Les campagnes historiques et ciblées
demeurent dans leurs certifications respectives ; elles ne sont pas fusionnées
avec ces exécutions finales.
