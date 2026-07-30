# Phase 5.1J — Quality Baseline

La certification finale exige :

- Architecture complète, zéro failure/error/risky ;
- suite applicative complète, zéro failure/error/risky ;
- PostgreSQL 18.x complète, sans fallback ;
- PHPStan, zéro erreur ;
- Pint ;
- `git diff --check` ;
- absence de modification fonctionnelle pendant 5.1J.

Les campagnes doivent être terminales. Un timeout, une interruption ou un test
concurrent instable n'est jamais retenu comme PASS. Une campagne remplacée doit
être réexécutée intégralement.

Les résultats définitifs sont consignés dans
`PHASE-5.1J-FINAL-CERTIFICATION.md`.

La réserve Outbox a été levée par
`A-5.1-IAM-OUTBOX-CONCURRENCY-01`, GO CERTIFIÉ et fermé. La campagne
PostgreSQL complète terminale de recertification est verte à 583 tests et
2 510 assertions.
