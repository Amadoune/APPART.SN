# PostgreSQL evidence

Suite ciblée sur la DB test isolée : 6 tests PASS, 20 assertions. Couverture : V1, V2 Applied/AlreadyApplied/Found, checksum/corruption, obsolete/divergent, rollback, concurrence et affected-terminal JSONB.

Aucune migration. La DB applicative n'a été mutée qu'après validations, par le catch-up certifié.
