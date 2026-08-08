# Phase 5.8B — Reliability & Operations — Compatibility Matrix

| Garantie | Qualification Discovery |
|---|---|
| Phase 5.8A inchangée et gelée | conforme |
| Owner candidat unique sans autorité métier | `ReliabilityOperations` |
| Séparation Runtime / Infrastructure / Operations | explicite |
| Observabilité sans payload métier | exigée |
| Health checks strictement techniques | exigés |
| Logs, traces et métriques minimisés | exigés |
| Alerting déterministe et attribué | exigé |
| Backup et restore prouvés | critère futur obligatoire |
| Disaster recovery avec RTO/RPO | qualification future obligatoire |
| Runbooks versionnés et auditables | critère futur obligatoire |
| Maintenance et housekeeping bornés | exigés |
| Queue operations sans mutation libre | exigées |
| Capacity planning avec hypothèses versionnées | exigé |
| Operational readiness sans décision métier | exigée |
| Foundation, code, migration et test | NON OUVERTS |

Le modèle est compatible avec les capacités gelées sous réserve de dépendances publiques, minimales et versionnées lors d'un futur jalon explicitement autorisé.
