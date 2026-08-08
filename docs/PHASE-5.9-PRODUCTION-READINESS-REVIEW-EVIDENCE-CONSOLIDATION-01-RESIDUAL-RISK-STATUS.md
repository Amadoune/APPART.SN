# Residual Risk Status

| Risque | Owner | Preuve attendue | Preuve observée | Emplacement | Statut | Justification | Action requise |
|---|---|---|---|---|---|---|---|
| Perte de données | Data Owner | backup+restore | aucune | aucun | BLOCKED | risque critique non accepté | qualification Backup/Restore/DR |
| Rollback impossible | Release Authority | exercice global | guides partiels | docs/*ROLLBACK* | BLOCKED | durée/intégrité inconnues | exercice Release/Rollback |
| Incident invisible | ReliabilityOperations | métriques+alertes testées | logs standard | config/logging.php | BLOCKED | détection insuffisante | qualification Observability |
| Saturation | ReliabilityOperations | charge et marges | aucune | aucun | BLOCKED | capacité inconnue | campagne Capacity |
| Compromission | SecurityCompliance | audits actuels et IAM prod | preuves historiques | docs/PHASE-5.8A-* | BLOCKED | posture prod non établie | qualification Security Readiness |
| Dépendance externe | Platform Owner | SLA/quota/fallback | configuration générique | .env.example | BLOCKED | disponibilité non démontrée | qualification External Dependencies |
| Erreur opérateur | Operations | runbooks exercés | fragments | docs/*GUIDE* | BLOCKED | opérations non standardisées | qualification Runbooks |
| Release non traçable | Change Manager | artefact+manifest+commit | aucun | aucun | BLOCKED | preuves non rattachables | Release Evidence Amendment |

Aucun de ces risques n'est accepté. Toute acceptation future doit nommer l'autorité, la portée, la durée, les compensations et la date d'expiration.

