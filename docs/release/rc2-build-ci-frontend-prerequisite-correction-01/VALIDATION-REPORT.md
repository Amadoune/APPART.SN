# Validation Report

| Validation | Résultat |
|---|---|
| Predecessor / futur tag | PASS |
| Workflow frontend avant suites | PASS |
| Build occurrences | 1 |
| Identity + ordering guards | PASS — 7 tests, 67 assertions |
| Bash syntax | PASS |
| Packaging alignment preflight | PASS — aucun artifact |
| Manifest avant build | absent |
| Vite production build | PASS |
| Manifest après build | présent et parseable |
| Feature historique | PASS — 1 test, 5 assertions |
| PHPStan ciblé | PASS — 0 erreur |
| Pint ciblé | PASS |
| Composer/package lock | inchangés |
| R5 / ancien tag actifs | 0 / 0 |
| Produit modifié | non |
| `git diff --check` | PASS |
| `git diff --cached --check` | PASS |
| Staging | 0 |

La campagne Reproducible Build complète n'est pas rejouée dans ce gate.
