# Phase 5.8B — Reliability & Operations — Dependency Matrix

| Dépendance candidate | Accès autorisable | Accès interdit | Condition future |
|---|---|---|---|
| Runtimes certifiés | disponibilité et diagnostics publics minimaux | objets internes et états métier | contrat versionné read-only |
| Infrastructure plateforme | métriques techniques et capacités déclarées | accès ad hoc aux stores owner-scoped | adaptateur nommé et borné |
| PostgreSQL | état technique agrégé, backup/restore outillé | tables métier, SQL libre et payloads | rôle dédié least-privilege |
| Queues | profondeur, âge, débit, état du worker | contenu des messages et mutation libre | commandes opérationnelles auditées |
| Logs | schéma technique minimisé | secret, PII et payload métier | filtrage et rétention certifiés |
| Traces | identifiants de corrélation techniques | SubjectKey et attributs métier | cardinalité et propagation bornées |
| Metrics | séries agrégées | labels sensibles ou non bornés | budget de cardinalité |
| Alerting | événements techniques dédupliqués | décision métier ou auto-remédiation implicite | règles fermées et escalade |
| Backup store | inventaire et preuves de restauration | extraction non autorisée | chiffrement et séparation des rôles |
| SecurityCompliance | politiques publiques applicables | composants, migrations 086–087 et données internes | dépendance contractuelle future uniquement |

## Dépendances interdites sans exception implicite

- accès direct aux Owner Sources, repositories, mappers et migrations des capacités gelées ;
- dépendance vers HTTP, Event, Delivery ou Outbox pour reconstruire un état métier ;
- écriture cross-owner ;
- transaction distribuée ;
- identifiants ou payloads métier dans les signaux opérationnels ;
- Provider, binding ou composant Laravel créé par ce Discovery.
