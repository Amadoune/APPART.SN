# Tool Authority Audit

| Contrôle | Résultat |
|---|---|
| Pint exécuté | `1.29.3` |
| Pint verrouillé | `laravel/pint v1.29.3`, source `da1d1111a6aa2e082d2a388b194afe1ba0a05d14` |
| Configuration | `pint.json`, preset `laravel` |
| Commande historique | `php vendor/bin/pint --test` |
| Commande actuelle | identique |
| Blob `composer.lock` R5/R8/R9/R10/HEAD | `1ff7511678e42503c3c518bb31a1d6d8f914020c` |
| Blob `composer.json` R5/R8/R9/R10/HEAD | `9e67585d5952d0695eef9ad7ea10b811cff07348` |

La version, le lockfile, la configuration et la commande n'ont pas dérivé. Le FAIL ne peut donc pas être classé comme défaut d'outil/version sur les preuves disponibles.

Aucune nouvelle exécution Pint n'a été nécessaire dans ce diagnostic : le résultat frais du gate H et les comparaisons Git suffisent.
