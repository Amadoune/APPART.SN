# Clean-room Build Evidence

La procédure et l'outillage sont versionnés. Les exécutions locales isolées doivent être consignées avec SHA, runtimes, exit codes, hash d'artefact et hash d'arbre.

Clone A : commit `3b15009b0615b0337b1cf19787736ba5632b8116`, baseline ancêtre vérifiée, checkout initial propre.

| Gate | Résultat | Preuve terminale |
|---|---|---|
| Composer/npm restore | PASS | 112 paquets Composer, 58 npm, plateforme et arbre validés |
| Unit | PASS | 2 872 tests, 10 779 assertions, exit 0 |
| Feature | PASS | 339 tests, 1 891 assertions, exit 0 |
| Architecture | FAIL | 908 tests, 903 PASS, 5 FAIL, 77 178 assertions, exit 1 |
| PostgreSQL/PHPStan/Pint/build/package | BLOCKED | non exécutés après l'échec bloquant |

Échecs Architecture : répertoire versionné `database/migrations` absent ; repository ExperienceAcceptance Outbox détecté deux fois (accès DB et SQL) hors allowlist ; repository concret absent de l'allowlist ; rollback 091 hors slices autorisées.

Une exécution locale isolée ne remplace pas la preuve CI externe ni la reproduction indépendante exigée.
