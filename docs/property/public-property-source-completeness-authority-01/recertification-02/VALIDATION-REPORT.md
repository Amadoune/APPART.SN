# Validation Report

Runtime : PHP Laragon 8.5.8 NTS.

| Campagne | Résultat | Tests | Assertions |
|---|---:|---:|---:|
| Source assembly PostgreSQL seule | PASS | 1 | 12 |
| F2/F3/F4/F5-A + RealEstateCatalog Domain/requirements | PASS | 67 | 302 |
| PostgreSQL Authoring 099 + Catalog + source assembly | PASS | 4 | 24 |
| F1/F4-A Unit, Feature et Architecture | PASS | 25 | 82 |
| PostgreSQL Geography Selection + Authoring Persistence | PASS | 8 | 50 |

Les campagnes ciblent exclusivement les autorités et persistences nécessaires à l’assemblage. Aucun test global n’a été relancé.

Qualité du test de certification ajouté :

- Pint ciblé : PASS ;
- PHPStan ciblé : PASS, 0 erreur ;
- `git diff --check` : PASS.
