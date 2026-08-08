# Dependency Restore Evidence

Empreintes de la baseline :

- `composer.lock` : `f15dde645598d805143d9ec1d3fab666730ac58ada078b0a3448c498bbd02be5` ;
- `package-lock.json` : `1a717514aba144013fe85101e951f18cc74de01f311c9f9b5378b767d00ed26a`.

La CI exécute `composer validate --strict`, `composer install` sans update, `composer check-platform-reqs`, `npm ci --ignore-scripts` et `npm ls --depth=0`. Le packaging restaure ensuite les dépendances PHP de production avec `--no-dev --classmap-authoritative` dans une racine release neuve.

Preuve terminale distante : `PENDING_EXECUTION`.
