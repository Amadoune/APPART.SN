# APPART.TEST Local Product Bootstrap 01 — Configuration Report

Date de validation : 2026-08-09.

## Résultat

`http://appart.test` répond avec HTTP 200 et affiche exclusivement la page technique locale :

- `APPART.TEST` ;
- `Bootstrap local OK` ;
- PHP 8.5.8 ;
- Laravel 13.20.0 ;
- environnement `local` ;
- PostgreSQL `connected`.

La route est protégée par une vérification explicite de l'environnement `local`.

## Configuration validée

| Élément | État | Preuve |
|---|---|---|
| URL locale | PASS | `http://appart.test`, HTTP 200 |
| PHP | PASS | CLI et Apache/FastCGI 8.5.8 |
| Laravel | PASS | Framework 13.20.0 |
| Apache | PASS | Apache 2.4.66, processus Laragon actifs |
| VirtualHost | PASS | `appart.test` → `C:/laragon/www/APPART-REBUILD/public` |
| hosts Windows | PASS | `127.0.0.1 appart.test` déjà présent |
| `.env` | PASS | configuration locale créée, non versionnée |
| `APP_KEY` | PASS | clé générée et présente, valeur non consignée |
| Driver PostgreSQL | PASS | `pgsql`, extensions `pdo_pgsql` et `pgsql` actives |
| PostgreSQL | PASS | 18.4, service automatique actif, connexion Laravel validée |
| Base locale | PASS | `appart_test` sur `127.0.0.1:5432` |
| Cache | PASS | config, routes et vues générées |
| Routes | PASS | route technique `/` et health `/up` disponibles |
| Health | PASS | `http://appart.test/up`, HTTP 200 |
| Assets | PASS | manifeste Vite présent, asset construit servi en HTTP 200 |
| Storage | PASS | junction `public/storage` créée |
| Permissions | PASS | écriture contrôlée dans `storage/framework/cache` |

## Limites respectées

Aucun Aggregate, UseCase, Repository métier, migration, Seeder, API métier ou module de Domaine n'a été créé ou modifié. La page n'effectue qu'un `SELECT 1` technique et n'expose aucun diagnostic de connexion.
