# APPART.TEST Public Search Results Read Model 01 — Validation Report

| Campagne | Résultat |
|---|---|
| Unit ciblé | PASS — 3 tests |
| Feature HTTP ciblé | PASS — 4 tests |
| Architecture ciblée | PASS — 2 tests |
| Ensemble rapide | PASS — 9 tests, 31 assertions |
| PostgreSQL ciblé | PASS — 3 tests, 7 assertions |
| PHPStan ciblé | PASS — 0 erreur |
| Pint ciblé | PASS |
| Route | PASS — `public-search.results` enregistrée une fois |
| Endpoint local vide | PASS — HTTP 200, `empty` |
| Migration | aucune créée ou modifiée |

Les tests PostgreSQL démontrent l'ordre canonique, deux pages successives, l'état vide et le fail-closed sur checksum corrompu. Le nettoyage restaure la base après chaque test.
