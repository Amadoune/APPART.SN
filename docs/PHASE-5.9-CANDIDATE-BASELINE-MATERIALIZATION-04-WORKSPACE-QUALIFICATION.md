# Candidate Baseline Materialization 04 — Workspace Qualification

## Ascendance

Le workspace de matérialisation part exactement de R4, commit `058719f8aa154466056299b8c26bd7d51f944127`. La candidate à créer doit en être une descendante directe sans réécriture d'histoire.

## Périmètre candidat

- trois contrôles Build/CI alignés sur R4 et le futur tag R5 ;
- test Architecture d'identité associé ;
- certification et modèle d'identité Correction 03 ;
- preuves historiques Evidence 06 ;
- clôture historique de Materialization 03 ;
- cinq registres normatifs synchronisés ;
- dossier de qualification et certification Materialization 04.

## Protections

Les migrations 090–091, leurs rollbacks, `composer.lock`, `package-lock.json`, R1, R2, R3 et R4 sont exclus du diff candidat et demeurent immuables.

Evidence 07 n'est pas ouverte et aucune preuve de ses futures campagnes n'est produite ou recyclée.

## Gates

- test d'identité ciblé : PASS — 3 tests, 27 assertions ;
- Architecture : PASS — 911 tests, 85 843 assertions ;
- contrôle syntaxique du script Packaging : PASS ;
- absence de référence R3 active dans les trois contrôles : PASS ;
- scan ciblé de secrets : PASS ;
- `git diff --check` : PASS.
