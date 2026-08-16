# Local vs Clean-room Matrix

| Configuration | Local | Clean-room R3 | Requise | Secrète |
|---|---|---|---|---|
| APP_ENV | `.env`/PHPUnit | PHPUnit | oui | non |
| APP_KEY | `.env` | absente | oui | valeur test non production |
| DB_CONNECTION/HOST/PORT/DATABASE/USERNAME | `.env` | absentes | oui | non sauf politique utilisateur |
| DB_PASSWORD | `.env` | absente | oui | oui |
| APPART_TEST_PG_* | processus | workflow | oui | password sensible |
| APPART_APPLICATION_PG_DATABASE | `.env` | workflow | oui | non |

Première différence autoritative : bootstrap Laravel `DB_*`/`APP_KEY` absent.
