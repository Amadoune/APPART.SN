# P02 — Validation Report

## Preuves produit terminales

| Contrôle | Résultat |
|---|---|
| `php artisan appart:local:first-listing` | PASS, exit code 0 |
| Replay idempotent | PASS, Registry et Workflow restent `published` |
| `GET http://appart.test/` | HTTP 200, annonce P02 présente |
| `GET /api/public-search/results` | HTTP 200, `status=available`, item P02 présent |
| `GET /annonces/p02-premiere-annonce-dakar` | HTTP 200 |
| `GET /p02-first-listing.svg` | HTTP 200 |
| Console navigateur | aucune erreur, aucun avertissement |
| Desktop | aucun débordement horizontal |
| Tablette 768 × 1024 | aucun débordement horizontal, annonce visible |
| Mobile 390 × 844 | aucun débordement horizontal, annonce et fiche visibles |

## Campagnes exécutées

| Campagne | Résultat terminal |
|---|---|
| Unit ciblé | PASS — 7 tests, 26 assertions |
| Feature ciblée | PASS — 28 tests, 84 assertions |
| Architecture ciblée | PASS — 18 tests, 7 130 assertions |
| PostgreSQL ciblé | PASS — 23 tests, 101 assertions |
| PHPStan ciblé séquentiel | PASS — 0 erreur |
| Pint ciblé | PASS |
| Vite | PASS — build produit généré |
| `git diff --check` | PASS |

Le premier lancement PHPStan parallèle a été interrompu par son timeout interne de worker après 600 secondes, sans diagnostic de code. La relance ciblée séquentielle a produit un résultat terminal PASS à 0 erreur.

## Intégrité du périmètre

- Aucun SQL direct dans la commande, l'UI ou les adapters.
- Aucun Seeder, aucune fixture de projection et aucun Aggregate publié forcé.
- Aucune modification de migration historique ; la migration 092 reste additive.
- Aucun staging, commit ou tag.
- Aucune campagne globale longue exécutée.
