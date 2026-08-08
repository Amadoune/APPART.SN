# Administration Console HTTP Foundation — Certification

## Objet certifiable

La façade HTTP contient trois Controllers invocables, trois Form Requests, une ResponseFactory, un marqueur HttpRuntime et un Service Provider unique.

Les quatorze statuts publics sont mappés exhaustivement vers HTTP 200, 404 ou 503. Le corps contient uniquement le statut public. Aucun Controller ne dépend de `AdministrationConsoleOwnerSource`, du Runtime interne, de PostgreSQL, d'un mapper ou d'Infrastructure.

## Composition certifiable

- quatre singletons lazy ;
- trois routes GET nommées et uniques ;
- un enregistrement du Provider dans `bootstrap/providers.php` ;
- trois dépendances Reader V1 publiques exclusivement ;
- migration 082 inchangée et protégée par ses empreintes SHA-256.

## Campagnes

| Campagne autorisée | Résultat |
|---|---|
| Unit + Feature HTTP + Architecture ciblés | PASS — 20 tests, 96 assertions |
| PHPStan | PASS — 0 erreur |
| Pint ciblé | PASS |
| `git diff --check` | PASS |

Aucun test PostgreSQL n'appartient à ce jalon.
