# Phase 5.8B — Reliability & Operations — Risk Register

| Risque | Impact | Qualification | Mesure exigée avant implémentation |
|---|---|---|---|
| Confusion disponibilité / readiness métier | décision erronée | élevé | catalogues séparés et preuve Architecture |
| Cardinalité métrique non bornée | saturation et coût | élevé | budgets et labels fermés |
| Fuite de PII ou secret dans logs/traces | sécurité et conformité | critique | minimisation, redaction et tests dédiés |
| Alert fatigue | incidents manqués | élevé | seuils, déduplication, ownership et revue |
| Health check en cascade | indisponibilité amplifiée | élevé | timeout, budget et dépendances explicites |
| Restore non vérifié | perte durable | critique | exercices réguliers et preuve d'intégrité |
| RTO/RPO non réalistes | reprise insuffisante | élevé | validation métier et technique |
| Runbook obsolète | erreur opérateur | élevé | version, owner, date de revue et exercice |
| Housekeeping destructif | perte de données | critique | dry-run, bornage, autorisation et rollback |
| Manipulation de queue non idempotente | duplication ou perte | critique | commandes fermées et preuves d'idempotence |
| Accès opérationnel excessif | compromission transverse | critique | least privilege, séparation des rôles et audit |
| Couplage cross-owner | transfert d'autorité | élevé | contrats publics minimaux uniquement |
| Supervision indisponible | perte de visibilité | élevé | monitoring indépendant et mode dégradé |
| Prévisions de capacité invalides | saturation | moyen | hypothèses versionnées et seuils de révision |
| Ouverture implicite de 5.8A | rupture du gel | critique | interdiction documentaire et contrôle de diff |

Aucun risque critique ne peut être accepté implicitement par ce Discovery.
