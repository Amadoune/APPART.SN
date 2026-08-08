# Responsibility Model

| Acteur | Responsabilité | Autorité conservée |
|---|---|---|
| ProductionReadinessReview | consolider preuves, écarts et verdict proposé | aucune décision métier ou exécution |
| Release Authority | prononcer GO / NO GO final | décision de mise en production |
| Owners de capacités | attester leurs baselines et invariants | décisions métier de leur domaine |
| SecurityCompliance | preuves sécurité, privacy et compliance | politique de sécurité certifiée |
| ReliabilityOperations | preuves supervision, sauvegarde, reprise et capacité | readiness opérationnelle certifiée |
| ExperienceAcceptance | UAT, E2E, accessibilité et performance utilisateur | critères d'acceptation certifiés |
| Équipe Operations | exécuter les runbooks autorisés | exploitation de la plateforme |
| Change Manager | manifeste, fenêtre, rollback et traçabilité | gouvernance du changement |

Aucune responsabilité ne justifie une modification technique pendant le Discovery.

