# R2 Clone Integrity

Contrôles depuis un clone neuf du tag R2 : status vide, `git diff --check`, présence de `database/migrations`, 40 fichiers 072–091, empreintes 090–091, lockfiles, scan secret, Architecture complète et PostgreSQL ciblé ExperienceAcceptance Outbox.

Résultats terminaux depuis `phase-5.9-baseline-candidate-r2` :

- HEAD : `5b1d0e647d1f74629b5f7e99e6f9d7e31941e988` ;
- status : vide ; `git diff --check` : PASS ;
- `database/migrations` : présent ; corpus 072–091 : 40 fichiers ;
- migrations 090–091 et rollbacks : quatre empreintes gelées inchangées ;
- `composer.lock` : `f15dde645598d805143d9ec1d3fab666730ac58ada078b0a3448c498bbd02be5` ;
- `package-lock.json` : `1a717514aba144013fe85101e951f18cc74de01f311c9f9b5378b767d00ed26a` ;
- scan secret de l'arbre Git : aucune occurrence ;
- Architecture : PASS — 908 tests, 85 816 assertions ;
- PostgreSQL ciblé Outbox 091 : PASS — 4 tests, 47 assertions.
