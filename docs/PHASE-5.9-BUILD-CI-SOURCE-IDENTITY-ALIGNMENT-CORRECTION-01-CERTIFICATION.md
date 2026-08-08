# Certification

Statut : `NO GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY`.

La correction remplace la confusion historique entre source de base et candidate exacte par une convention sans auto-référence : R2 est l'ancêtre immuable ; le tag annoté R3 doit résoudre exactement vers le commit construit.

La preuve ciblée est PASS, ainsi que Unit, Feature, Architecture et Foundation globales. PostgreSQL global est terminal `FAIL` : 763 tests, 759 PASS, 3 586 assertions et 4 erreurs de dépendance de schéma entre OwnerSource 090 et Outbox 091.

Conformément au fail-fast, PHPStan, Pint, frontend, matérialisation/tag R3 et clean-room sont `BLOCKED`. Aucun commit ni tag R3 n'est créé. La première divergence terminale est hors du périmètre technique de cet amendement et n'est pas corrigée opportunément.

Verdict : `NO GO PROPOSÉ — PHASE-5.9-BUILD-CI-SOURCE-IDENTITY-ALIGNMENT-CORRECTION-01`.
