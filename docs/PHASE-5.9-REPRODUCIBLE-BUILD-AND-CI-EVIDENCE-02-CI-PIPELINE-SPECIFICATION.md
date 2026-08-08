# CI Pipeline Specification

Pipeline : `.github/workflows/phase-5.9-reproducible-build.yml`.

Le job unique rattache toutes les gates au même `github.sha`, vérifie que la baseline certifiée est son ancêtre, utilise PostgreSQL 18.4 par digest et exécute : restauration verrouillée, Unit, Feature, Architecture, Foundation, PostgreSQL, PHPStan, Pint, `git diff --check`, build Vite, packaging, checksums, manifeste et archivage de preuve.

Actions immuables : checkout `11d5960…`, setup-node `49933ea…`, setup-php `f3e473d…`, upload-artifact `ea165f8…`.

Aucune étape non exécutée ne peut produire de manifeste : `set -euo pipefail` et les dépendances GitHub Actions imposent l'arrêt au premier échec.
