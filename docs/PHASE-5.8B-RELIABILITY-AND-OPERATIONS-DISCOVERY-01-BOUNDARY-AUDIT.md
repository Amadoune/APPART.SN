# Phase 5.8B — Reliability & Operations — Boundary Audit

## Frontière retenue

`ReliabilityOperations` qualifie la capacité à observer, superviser et préparer l'exploitation technique. Il ne devient ni propriétaire des états métier, ni orchestrateur fonctionnel, ni source de vérité des capacités observées.

## Inclus

| Surface | Responsabilité candidate |
|---|---|
| Metrics | mesures techniques agrégées, cardinalité bornée, SLI/SLO candidats |
| Health checks | liveness, readiness technique et dépendances explicites |
| Logs | événements techniques minimisés, structurés et expurgés |
| Traces | causalité technique avec propagation contrôlée du contexte |
| Alerting | règles déterministes, déduplication, escalade et acquittement |
| Supervision | vue d'état technique et historique opérationnel |
| Backup / Restore | politiques, inventaire, preuves et exercices |
| Disaster recovery | RTO/RPO candidats, scénarios et responsabilités |
| Runbooks | procédures versionnées, préconditions, rollback et preuves |
| Maintenance / Housekeeping | opérations bornées, réversibles et auditables |
| Queue operations | profondeur, âge, saturation, pause/reprise autorisée |
| Capacity planning | tendances, seuils et hypothèses documentées |
| Operational readiness | checklist technique sans décision métier |

## Exclus

- décisions métier, statuts métier et agrégations cross-owner ;
- lecture directe des stores owner-scoped ou des payloads métier ;
- secrets, clés, PII, SubjectKey, contenu d'audit métier et données réglementées ;
- mutation implicite de Runtime, queue, base ou infrastructure ;
- Transport, Routing, Consumer et toute nouvelle migration ;
- remédiation automatique non bornée ou non autorisée.

## Séparation des plans

| Plan | Rôle | Limite |
|---|---|---|
| Runtime | expose sa disponibilité technique locale | aucune exploitation ni décision métier |
| Infrastructure | fournit les mécanismes techniques | aucune politique transverse implicite |
| Operations | observe, alerte et exécute des procédures autorisées | aucun contournement des owners ou du Runtime |

## Conclusion de frontière

La frontière est qualifiable sans implémentation. Toute matérialisation future nécessite une décision distincte et un contrat minimal explicitement certifié.
