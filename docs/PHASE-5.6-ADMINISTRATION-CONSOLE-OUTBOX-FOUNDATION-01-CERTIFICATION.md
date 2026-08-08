# Administration Console Outbox Foundation — Certification

## Objet certifiable

La Foundation contient cinq composants Application, un Repository PostgreSQL, une migration additive 083 et son rollback.

Les garanties certifiables comprennent : journal append-only owner-scoped, message unique par Delivery, identité et checksum SHA-256 canoniques, idempotence, divergence explicite, lecture ordonnée, retry borné à dix, concurrence déterministe par conflit de clé, savepoints locaux, rollback externe préservé et reconstruction UTC canonique.

## Campagnes

| Campagne autorisée | Résultat |
|---|---|
| Unit + Architecture ciblés | PASS — 16 tests, 110 assertions |
| PostgreSQL ciblé | PASS — 2 tests, 16 assertions |
| PHPStan | PASS — 0 erreur |
| Pint ciblé | PASS |
| `git diff --check` | PASS |

Aucune Feature HTTP n'appartient à ce jalon.
