# Runbook Inventory

| Exigence | Owner | Preuve attendue | Preuve observée | Emplacement | Statut | Justification | Action requise |
|---|---|---|---|---|---|---|---|
| Déploiement | Operations | étapes, validations, arrêt | aucune | aucun | MISSING | non opérable | runbook requis |
| Rollback | Operations | application+données | guides fragmentaires | docs/*ROLLBACK* | PARTIAL | non consolidé/exercé | runbook global |
| Workers | Operations | start/stop/drain/replay | aucune | aucun | MISSING | opérations de file non bornées | runbook requis |
| Incident | Incident Commander | triage/escalade/communication | aucune | aucun | MISSING | aucune procédure | runbook requis |
| DB | Data Owner | saturation, lock, failover | diagnostics historiques | docs, tests PostgreSQL | PARTIAL | pas de procédure production | runbook DB |
| Backup/restore | Data Owner | exécution et validation | aucune | aucun | MISSING | récupération non opérable | runbook et exercice |
| Secrets | SecurityCompliance | rotation/révocation/break-glass | aucune | aucun | MISSING | incident secret non opérable | runbook sécurité |
| Maintenance | Operations | housekeeping et fenêtre | aucune | aucun | MISSING | dette opérationnelle | calendrier et procédure |

