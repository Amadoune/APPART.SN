# Phase 5.8B — Reliability & Operations — Responsibility Model

| Domaine | ReliabilityOperations | Owner métier | Infrastructure | SecurityCompliance |
|---|---|---|---|---|
| SLI/SLO techniques | propose, mesure, rapporte | valide l'impact de service | expose les signaux autorisés | qualifie confidentialité et intégrité |
| Health checks | définit la sémantique technique | ne délègue aucune décision métier | exécute les sondes locales | contrôle l'exposition |
| Logs et traces | normalise, minimise, corrèle | conserve son autorité | émet et transporte selon contrat | définit contraintes de protection |
| Alerting | route l'alerte et l'escalade | reçoit les alertes pertinentes | fournit les signaux | contrôle accès et rétention |
| Backup / Restore | coordonne politique et preuve | approuve les exigences de données | exécute les mécanismes | contrôle chiffrement et accès |
| Disaster recovery | coordonne scénarios et exercices | approuve RTO/RPO métier | restaure les services | participe à la gestion d'incident |
| Runbooks | versionne et attribue | valide les étapes touchant son domaine | exécute les actions autorisées | valide les étapes sensibles |
| Queue operations | supervise et applique les procédures bornées | décide des effets métier | expose les commandes autorisées | audite les accès sensibles |
| Capacity planning | consolide tendances techniques | fournit prévisions métier | fournit limites et consommation | interdit l'exposition sensible |
| Operational readiness | consolide les preuves | atteste la préparation métier | atteste la préparation technique | atteste les contrôles de sécurité |

## Règles d'autorité

- `ReliabilityOperations` ne décide jamais de la validité, complétude ou transition d'un état métier.
- Une alerte est un signal opérationnel, pas une décision métier.
- Un health check atteste seulement un état technique défini par contrat.
- Restore, purge, pause de queue et reprise sont des opérations privilégiées soumises à autorisation et audit.
- SecurityCompliance reste owner de ses politiques de sécurité, confidentialité et conformité ; la Phase 5.8A demeure gelée.
