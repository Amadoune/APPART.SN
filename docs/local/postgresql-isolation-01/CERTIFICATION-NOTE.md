# Certification Note

## Réalisé

- convention `appart_rebuild` / `appart_test` arrêtée ;
- garde fail-safe ajoutée aux connexions et resets PostgreSQL ;
- configuration exemple et CI explicites ;
- commandes locales alignées ;
- collision historique désormais refusée avant mutation.

## Blocage unique

La base locale `appart_rebuild` ne peut pas être créée avec le rôle disponible `appart_test`, qui n'a pas `CREATEDB`. Sans intervention d'une autorité PostgreSQL locale, `.env` ne peut pas être basculé et la preuve sentinelle A/B ne peut pas être exécutée.

## Verdict

**NO GO PROPOSÉ — RC2 / POSTGRESQL LOCAL APPLICATION & TEST DATABASE ISOLATION 01.**

## Reopening 01 — Application Database Materialization

Le préflight confirme PostgreSQL 18.4, `appart_test` existante, `appart_rebuild` absente et `appart_test` sans privilège `CREATEDB`. Aucun credential administrateur local autorisé n'est disponible.

La règle fail-closed interdit de modifier l'authentification, le password ou les privilèges pour contourner ce gate. La base A ne peut donc pas être créée, migrée ni soumise à la preuve sentinelle ; `.env` reste volontairement inchangé.

**NO GO PROPOSÉ — LOCAL APPLICATION & TEST DATABASE ISOLATION 01 — REOPENING 01.**

Cause unique restante : accès administrateur PostgreSQL local autorisé requis pour créer `appart_rebuild` avec owner `appart_test`.

## Reopening 02 — Admin Materialization + Terminal Certification

La découverte autorisée confirme qu'aucune connexion administrative authentifiée n'est disponible. Le rôle `postgres` existe, mais pgAdmin ne possède aucun password sauvegardé ; aucun `pgpass`, credential Windows ou environnement administratif n'est présent.

Conformément au cas B, la campagne s'arrête avant toute création, bascule, migration, sentinelle ou recertification Resume.

**NO GO PROPOSÉ — LOCAL APPLICATION & TEST DATABASE ISOLATION 01 — ADMIN ACCESS REQUIRED.**

## Reopening 03 — Terminal Certification

La preuve opérateur a levé le blocker administratif. `appart_rebuild` existe, appartient à `appart_test`, utilise UTF8, et `appart_test` reste sans CREATEDB/superuser.

L'application pointe sur `appart_rebuild`; les suites destructives restent sur `appart_test`. Les 81 migrations certifiées jusqu'à `100_property_promotion.sql` ont été appliquées à la base A sans copie de données. La sentinelle A/B, le test négatif, les campagnes PostgreSQL et le smoke HTTPS sont PASS.

**GO PROPOSÉ — LOCAL APPLICATION & TEST DATABASE ISOLATION 01 — RECERTIFIÉE / FERMÉE.**
