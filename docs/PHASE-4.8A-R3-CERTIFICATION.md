# Phase 4.8A-R3 — Replay Inspection and Target Evidence Boundary Certification

## Livrables

- clarification normative de l'inspection de rejeu;
- attribution unique de `TargetMissing`;
- matrice corrigée des responsabilités;
- ordre de précédence corrigé;
- roadmap 4.8 synchronisée.

## Critères satisfaits par conception

- `Inspection::Missing` signifie exclusivement absence d'historique de rejeu;
- `Missing` poursuit le chemin nominal;
- Target Evidence Qualification possède seule `TargetMissing`;
- cette frontière reçoit réellement `targetId` et l'autorité d'existence;
- 4.8E reçoit uniquement un contexte V1 déjà qualifié;
- aucune couche ne reçoit une décision indérivable de ses entrées;
- `PlaceMergeContextV1` et tous les contrats certifiés restent inchangés;
- aucune implémentation technique n'est introduite.

## Verdict

**GO CERTIFIÉ**.

R3 est fermé et gelé. L'ouverture de 4.8E a ensuite été suspendue avant
implémentation, car l'action historique est absente du résultat d'inspection.
Le seul sprint autorisé est **4.8A-R4 — Replay Attempt Identity Amendment**.
