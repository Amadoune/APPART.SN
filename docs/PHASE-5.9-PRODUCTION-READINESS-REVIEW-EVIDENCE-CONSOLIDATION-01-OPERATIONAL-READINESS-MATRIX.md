# Operational Readiness Matrix

| Exigence | Owner | Preuve attendue | Preuve observée | Emplacement | Statut | Justification | Action requise |
|---|---|---|---|---|---|---|---|
| Runtime configuration | Platform Owner | matrice validée | exemple local | .env.example | PARTIAL | aucune configuration prod attestée | qualifier sans exposer les secrets |
| Runtime Health | ReliabilityOperations | probes et exercice | Runtime Availability interne | src/Modules/*/Application/Runtime | PARTIAL | aucune intégration plateforme démontrée | preuve de liveness/readiness |
| Workers | Operations | supervisor et runbook | code worker présent | app/Infrastructure/PublicProjectionWorker | PARTIAL | démarrage, arrêt et reprise non documentés | produire runbook |
| Retry/quarantine/replay | Domain Owners | exercice et procédure | code/tests présents | app/Application ; tests | PARTIAL | preuve opérationnelle absente | exercice séparé |
| Maintenance | Operations | fenêtres et responsabilités | non observé | aucun | MISSING | aucune gouvernance d'intervention | définir calendrier/RACI |
| Capacité | ReliabilityOperations | charge et marges | non observé | aucun | MISSING | seuils inconnus | campagne dédiée |
| Incident response | Incident Commander | runbook et exercice | non observé | aucun | MISSING | réponse non démontrée | qualification séparée |

