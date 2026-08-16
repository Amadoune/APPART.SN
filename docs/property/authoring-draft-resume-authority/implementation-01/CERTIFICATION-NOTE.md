# Certification Note

## Capacités démontrées

- sélection explicite par ListingId ;
- AccountId session-only et Portfolio owner-scoped ;
- Reader productif avec résultats fermés ;
- refus cross-owner ;
- conservation des IDs et versions sur fixture réelle ;
- step, Geography et Media metadata restaurés ;
- GET Resume sans mutation ;
- reload/réauthentification supportés par la route dédiée et le bootstrap ;
- aucune migration, F6/F7, Projection ou Search ;
- campagnes qualité PASS.

## Blocage unique

Le draft RC2 obligatoire n'existe plus dans la base locale après une campagne PostgreSQL non isolée (`APPART_TEST_PG_DSN` et l'application ciblent `appart_test`). Sa reprise réelle ne peut donc pas être certifiée sans restauration/provisionnement autorisé, hors périmètre de cette mission.

## Verdict

**NO GO PROPOSÉ — APPART.SN AUTHORING / RC2 CONTINUITY — AUTHORING DRAFT RESUME IMPLEMENTATION 01.**

## Recertification 01

La base applicative et la base de tests sont désormais matériellement isolées. Sans reconstruire les anciens IDs, un nouveau draft productif owner-scoped a été créé avec Property, Listing Draft, Geography Dakar City et Media réel.

La route Resume retourne HTTP 200, restaure exactement PropertyId `5797a9b5-088d-43ea-8854-cac441946f6b`, ListingId `979cd5aa-ced1-48a1-8adf-8b29c843a0c2`, versions 1/1, step 6, Geography et un média. Reload et campagne PostgreSQL destructive sur DB B laissent le fingerprint DB A inchangé.

**GO PROPOSÉ — AUTHORING DRAFT RESUME IMPLEMENTATION 01 — RECERTIFIÉE / FERMÉE.**
