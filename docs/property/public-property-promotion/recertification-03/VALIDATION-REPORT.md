# Rapport de validation F7 Reopening 02

## Campagne terminale

Environnement : PHP 8.5.8, PostgreSQL local qualifié.

| Campagne | Résultat |
|---|---|
| F7-B canonical compatibility | PASS |
| F7-A rollback/retry et Submit | PASS |
| F6 Promotion et migration 100 | PASS |
| Geography Catalog et persistance, positif/négatif | PASS |
| F2 Address Identity | PASS |
| F3 Business Year UTC | PASS |
| Property Domain / ChangeAddress | PASS |
| Architecture Promotion, Submit, F2, F3 et Geography | PASS |
| PHPUnit combiné | PASS — 109 tests, 570 assertions |
| PHPStan ciblé | PASS — 0 erreur |
| Pint ciblé | PASS |
| `git diff --check` | PASS |

## Contrôles statiques

- aucune dépendance Promotion vers Projection/Search/Public Listing/HTTP Geography ;
- aucune migration 101 ;
- migration 100 up/down et réapplication couvertes ;
- aucune modification produit ou test pendant cette campagne.
