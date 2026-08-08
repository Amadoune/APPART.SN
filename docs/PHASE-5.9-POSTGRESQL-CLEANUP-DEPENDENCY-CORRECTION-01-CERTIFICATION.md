# Certification

Statut : `GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY`.

Cause : ordre de nettoyage incomplet dans `PostgreSqlExperienceAcceptanceOwnerSourceTest` pour le schéma partagé par 090 et 091.

Correctif : rollback 091 avant rollback 090 dans le setup de test.

Preuves :

- Outbox 091 ciblé : PASS — 4 tests, 47 assertions ;
- OwnerSource 090 ciblé après Outbox : PASS — 4 tests, 37 assertions ;
- première campagne globale : les quatre erreurs 090/091 ont disparu ; un test concurrent Listing sans lien avec le diff a échoué transitoirement ;
- test Listing concurrent ciblé : PASS — 2 tests, 10 assertions ;
- campagne globale de vérification : PASS — 763/763 tests, 3 623 assertions, 948,322 s, exit code 0.

Migrations 090–091, rollbacks, lockfiles, Build/CI et modèle d'identité ne sont pas modifiés par ce correctif.

Verdict : `GO PROPOSÉ — PHASE-5.9-POSTGRESQL-CLEANUP-DEPENDENCY-CORRECTION-01`.
