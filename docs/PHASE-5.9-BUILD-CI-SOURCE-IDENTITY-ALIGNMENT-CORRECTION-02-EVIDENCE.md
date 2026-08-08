# Build/CI Alignment Evidence

| Exigence | Preuve | Statut |
|---|---|---|
| Runtime lock | R2 source ancestrale + tag candidat R3 + politique explicite | PASS |
| Workflow | tag annoté exact = commit checkouté ; R2 ancêtre | PASS |
| Packaging | tag annoté exact = HEAD ; R2 ancêtre | PASS |
| Auto-référence | aucune SHA R3 dans le contenu | PASS |
| Candidate incorrecte | refus par égalité exacte du tag et contrôle d'ascendance | PASS — politique fermée, sans wildcard/fallback |
| Gates globales | toutes terminales et vertes | PASS |
| R3/tag/clone neuf | interdits dans ce jalon | NON EXÉCUTÉ |

## Résultats terminaux

| Gate | Résultat |
|---|---|
| Identité ciblée | PASS — 3 tests, 25 assertions |
| Unit | PASS — 2 872 tests, 10 779 assertions |
| Feature | PASS — 339 tests, 1 891 assertions |
| Architecture | PASS — 911 tests, 85 841 assertions |
| Foundation | PASS — 1 test, 4 assertions |
| PostgreSQL | PASS — 763 tests, 3 623 assertions, exit code 0 |
| PHPStan | PASS — 0 erreur |
| Pint global | PASS |
| Frontend | PASS |
| `git diff --check` | PASS |
| Scan de secrets ciblé | PASS — aucun motif détecté |

Le build frontend ne produit aucun diff. Migrations 090–091, rollbacks, lockfiles et artifacts frontend restent inchangés.
