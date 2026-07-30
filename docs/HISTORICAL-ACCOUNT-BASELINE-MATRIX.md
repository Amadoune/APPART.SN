# Historical Account — Baseline Matrix

Les résultats ci-dessous sont les baselines certifiées des jalons. La
certification finale les consolide; elle ne les présente pas comme une nouvelle
campagne d'implémentation.

| Jalon | Campagne | Tests | Assertions | Statut |
|---|---|---:|---:|---|
| 4.9P-B | Architecture complète | 555 / 555 | 43 330 | PASS |
| 4.9P-B | Suite complète | 2 646 / 2 646 | 51 042 | PASS |
| 4.9P-C | PostgreSQL complète | 555 / 555 | 2 355 | PASS |
| 4.9P-C | Architecture complète | 560 / 560 | 43 426 | PASS |
| 4.9P-C | Suite complète | 2 651 / 2 651 | 51 138 | PASS |
| 4.9P-D | Architecture complète | 564 / 564 | 43 448 | PASS |
| 4.9P-D | Suite complète | 2 657 / 2 657 | 51 172 | PASS |
| 4.9P-D | PostgreSQL complète | 555 / 555 | 2 355 | PASS |

| Contrôle transversal | Résultat |
|---|---|
| PHPStan | 0 erreur |
| Pint | PASS |
| `git diff --check` | PASS |
| Runtime Health | Healthy — 55 capacités, catalogue inchangé |

## Règle de baseline

La baseline de sortie de 4.9P est celle de 4.9P-D. Les baselines antérieures
restent enregistrées comme preuves incrémentales et ne sont ni remplacées ni
réinterprétées.
