# Phase 4.7A-R1 — Four-Eyes Decision Matrix

| Disposition | Relation requise | Usage autorisé | Construction incohérente |
|---|---|---|---|
| DirectRecording | auteur = acteur de décision | `Record` vers `Recorded` | acteur distinct refusé |
| IndependentApprovalRequired | auteur ≠ acteur de décision | `Record` vers `PendingApproval`, puis décision indépendante | auto-approbation/rejet refusé |

Cette matrice certifie uniquement la preuve d'autorité. La matrice état/action appartient au futur Workflow 4.7A.
