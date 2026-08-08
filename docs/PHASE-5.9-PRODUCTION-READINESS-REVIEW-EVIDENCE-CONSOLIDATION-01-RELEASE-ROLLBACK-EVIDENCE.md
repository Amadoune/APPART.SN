# Release / Rollback Evidence

| Exigence | Owner | Preuve attendue | Preuve observée | Emplacement | Statut | Justification | Action requise |
|---|---|---|---|---|---|---|---|
| Artefact release | Release Authority | paquet immuable checksumé | aucun | aucun | MISSING | pas de candidat certifiable | générer hors de ce jalon |
| Build CI | Quality | pipeline clean-room | scripts locaux | composer.json, package.json | PARTIAL | absence de CI | créer preuve reproductible séparée |
| Manifeste | Change Manager | versions, migrations, checksums | manifests historiques seulement | docs/PHASE-5.3L-BASELINE-MANIFEST.csv | PARTIAL | obsolète pour 5.9 | manifeste nouveau requis |
| Déploiement | Operations | procédure et fenêtre | non observé | aucun | MISSING | rollout non borné | runbook de déploiement |
| Rollback application | Operations | exercice du candidat | non observé | aucun | MISSING | retour binaire non démontré | exercice séparé |
| Rollback données | Data Owner | ordre, sauvegarde et exercice | guides par migration | docs/*ROLLBACK* | PARTIAL | pas de preuve globale récente | répétition production-like |
| Décision d'arrêt | Release Authority | seuils et autorité | critères génériques Discovery | docs/PHASE-5.9-*-EVIDENCE-MATRIX.md | PARTIAL | autorité non nommée | décision explicite requise |

