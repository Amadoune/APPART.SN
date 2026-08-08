# Administration Console — Evidence Index

## Jalons certifiés

1. `PHASE-5.6-ADMINISTRATION-CONSOLE-DISCOVERY-01` ;
2. `PHASE-5.6-ADMINISTRATION-CONSOLE-CONTRACTS-FOUNDATION-01` ;
3. `PHASE-5.6-ADMINISTRATION-CONSOLE-PERSISTENCE-FOUNDATION-01` ;
4. `PHASE-5.6-ADMINISTRATION-CONSOLE-RUNTIME-FOUNDATION-01` ;
5. `PHASE-5.6-ADMINISTRATION-CONSOLE-OWNER-READER-BOUNDARY-01` ;
6. `PHASE-5.6-ADMINISTRATION-CONSOLE-OWNER-READER-FOUNDATION-01` ;
7. `PHASE-5.6-ADMINISTRATION-CONSOLE-HTTP-FOUNDATION-01` ;
8. `PHASE-5.6-ADMINISTRATION-CONSOLE-EVENT-FOUNDATION-01` ;
9. `PHASE-5.6-ADMINISTRATION-CONSOLE-DELIVERY-FOUNDATION-01` ;
10. `PHASE-5.6-ADMINISTRATION-CONSOLE-OUTBOX-FOUNDATION-01`.

Chaque dossier contient sa certification et ses matrices propres. Les Boundary Audits contiennent leurs analyses d'autorité, inventaires de source, matrices de dépendance et registres de risques.

## Preuves structurantes

- owner unique : `AdministrationConsole` ;
- source owner unique : `AdministrationConsoleOwnerSource` ;
- Readers publics : Operator, Queue et Audit V1 ;
- migrations : 082 owner source et 083 outbox, avec rollbacks dédiés ;
- tests : Unit, Architecture, Feature HTTP et PostgreSQL ciblés selon les autorisations de chaque jalon ;
- qualité statique : PHPStan et Pint acquis dans les Foundations techniques ;
- intégrité documentaire : roadmaps, amendment register et changelog alignés.

Les preuves techniques certifiées ne sont pas rejouées par le gel final.
