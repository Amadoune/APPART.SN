# External CI — Workflow and Trigger Audit

Workflow audité : `.github/workflows/phase-5.9-reproducible-build.yml` tel qu'il existe dans R5.

## Déclencheurs

- push du tag exact `phase-5.9-baseline-candidate-r5` ;
- `workflow_dispatch` manuel.

## Identité et sécurité

- checkout épinglé par SHA, profondeur complète ;
- validation d'un objet tag annoté ;
- validation `tag^{commit} = GITHUB_SHA` ;
- ascendance depuis R4 contrôlée ;
- permission minimale `contents: read` ;
- actions tierces épinglées par SHA.

## Gates et preuves

Le job `certify`, sur `ubuntu-24.04`, exécute restauration, Unit, Feature, Architecture, Foundation, PostgreSQL, PHPStan, Pint, frontend et packaging. L'artefact `dist/release/` est conservé 30 jours par `upload-artifact`.

## Qualification

Le workflow est structurellement compatible avec une exécution R5 exacte. Son exécution réelle reste `BLOCKED` par l'absence de repository distant officiel et de canal d'exécution externe qualifié.
