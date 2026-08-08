# CI Pipeline Specification

Source exclusive : `phase-5.9-baseline-candidate-r2` à `5b1d0e647d1f74629b5f7e99e6f9d7e31941e988`.

Ordre fail-fast : identité, propreté, verrou Runtime, restauration, Unit, Feature, Architecture, PostgreSQL, PHPStan, Pint, frontend, artifact, deux packagings, comparaison, SHA-256, manifeste, CI externe, reproduction indépendante, certification.

Une porte rouge bloque toutes les suivantes. La porte Runtime est `FAIL`; aucune porte à compter de la restauration n'a été exécutée.
