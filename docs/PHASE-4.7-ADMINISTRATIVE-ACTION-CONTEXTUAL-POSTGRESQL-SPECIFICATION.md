# Phase 4.7C-R2 — Contextual PostgreSQL Specification

La migration additive **035** crée une table append-only identifiée par `(action_id, version)`.

Elle conserve le contexte V1 exact : version attendue, acteur, instant, action, identités de décision, motif historique, Decision Context V1, checksum du Decision Context et checksum contextuel.

Les contraintes garantissent :

* `version = expected_version + 1` ;
* formes fermées des identités `Record`, `Approve`, `Reject` ;
* cohérence de l'acteur avec l'auteur ou le décideur ;
* versions contractuelles V1 ;
* checksums SHA-256 ;
* unicité d'un contexte par append.

Aucune clé étrangère ne bloque le rollback autonome de 034. Le lien exact est vérifié par l'identité/version et par l'inspection jointe.
