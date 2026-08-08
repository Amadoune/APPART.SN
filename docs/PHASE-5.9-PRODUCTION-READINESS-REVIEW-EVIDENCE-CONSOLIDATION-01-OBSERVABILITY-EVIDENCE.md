# Observability Evidence

| Exigence | Owner | Preuve attendue | Preuve observée | Emplacement | Statut | Justification | Action requise |
|---|---|---|---|---|---|---|---|
| Logs | Operations | backend, rétention, corrélation | config Laravel | config/logging.php | PARTIAL | aucune collecte production attestée | preuve de bout en bout |
| Métriques | ReliabilityOperations | catalogue et dashboards | aucune | aucun | MISSING | instrumentation non prouvée | diagnostic technique séparé |
| Traces | ReliabilityOperations | propagation et backend | aucune | aucun | MISSING | diagnostic distribué absent | qualifier l'applicabilité |
| Alerting | ReliabilityOperations | règles, owners, test | aucune | aucun | MISSING | détection incident non démontrée | work package séparé |
| Runtime Health | ReliabilityOperations | probes plateforme | contrats internes | src/Modules/ReliabilityOperations | PARTIAL | pas de sonde déployée prouvée | attestation plateforme |
| Audit sécurité | SecurityCompliance | collecte immuable et accès | structures certifiées | src/Modules/SecurityCompliance | PARTIAL | backend/rétention non démontrés | preuve opérationnelle |

