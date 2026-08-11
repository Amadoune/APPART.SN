# Public Data Restoration 01 — Validation Report

## Preuves réelles

| Contrôle | Résultat |
|---|---|
| Commande certifiée | PASS — exit code 0 |
| Recherche `sale / Dakar / apartment` | PASS — 1 résultat réel |
| CanonicalPath | PASS — `annonces/p03-appartement-a-vendre-dakar` |
| Fiche locale | PASS — HTTP 200 |
| Canonical publique | PASS — `https://appart.sn/annonces/p03-appartement-a-vendre-dakar` |
| Robots fiche | PASS — `index, follow` |
| Open Graph | PASS |
| Schema.org | PASS — un bloc JSON-LD |
| Sitemap | PASS — P02 et P03 présentes |
| Feature ciblée | PASS — 11 tests, 80 assertions |
| PostgreSQL ciblé | PASS — 4 tests, 12 assertions |
| git diff --check | PASS |

La campagne PostgreSQL ciblée ayant nettoyé son schéma partagé, la commande certifiée a été rejouée après les tests. Le contrôle HTTP final confirme toujours un résultat réel.

PHPStan et Pint n'ont pas été exécutés : aucune source PHP n'a été modifiée par ce chantier.
