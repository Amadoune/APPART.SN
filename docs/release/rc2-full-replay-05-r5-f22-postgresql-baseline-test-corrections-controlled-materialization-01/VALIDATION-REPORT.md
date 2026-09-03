# Validation report

## Required gates

- PostgreSQL migration 058;
- PostgreSQL MediaIngestion targeted;
- PostgreSQL PublicProjectionOutbox;
- related transaction Unit/Architecture tests;
- PostgreSQL complete;
- F22 targeted Architecture, Unit, and Feature tests;
- PHPStan complete;
- Pint on every modified PHP file;
- exact delta audit and `git diff --check`.

## Results on the materialized source

- PostgreSQL migration 058: PASS — 2 tests, 10 assertions;
- PostgreSQL MediaIngestion targeted: PASS — 14 tests, 84 assertions;
- PostgreSQL PublicProjectionOutbox: PASS — 41 tests, 338 assertions;
- related transaction Unit/Architecture tests: PASS — 19 tests, 196 assertions;
- PostgreSQL complete: PASS — 815 tests, 3985 assertions;
- F22 targeted Architecture: PASS — 4 tests, 99 assertions;
- F22 relevant Unit: PASS — 11 tests, 36 assertions;
- F22 relevant Feature: PASS — 4 tests, 40 assertions;
- PHPStan complete: PASS — 0 errors;
- Pint on every modified PHP file: PASS;
- exact delta audit and `git diff --check`: PASS.

These results belong to this materialized source only. The terminal handoff
confirms that the committed tree is identical to the qualified index tree.
