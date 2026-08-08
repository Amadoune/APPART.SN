# Dependency Restore Evidence

Empreintes de la baseline :

- `composer.lock` : `f15dde645598d805143d9ec1d3fab666730ac58ada078b0a3448c498bbd02be5` ;
- `package-lock.json` : `1a717514aba144013fe85101e951f18cc74de01f311c9f9b5378b767d00ed26a`.

La CI exécute `composer validate --strict`, `composer install` sans update, `composer check-platform-reqs`, `npm ci --ignore-scripts` et `npm ls --depth=0`. Le packaging restaure ensuite les dépendances PHP de production avec `--no-dev --classmap-authoritative` dans une racine release neuve.

Preuve locale clean-room : PASS après activation explicite de l'extension ZIP du runtime 8.5.8 ; 112 paquets Composer et 58 paquets npm restaurés, `composer check-platform-reqs` et `npm ls --depth=0` PASS. npm signale une vulnérabilité haute dans l'arbre verrouillé ; aucune mise à jour opportuniste n'est appliquée.

Preuve terminale distante : `MISSING`.
