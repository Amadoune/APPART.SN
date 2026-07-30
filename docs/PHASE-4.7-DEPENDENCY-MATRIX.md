# Phase 4.7 — Dependency Matrix

| Dépendance | Propriétaire | Usage futur autorisé | Usage interdit | Gate |
|---|---|---|---|---|
| statuts et politiques historiques | `AdministrationAudit` | preuve de cohérence lors du blueprint | appel de l'Aggregate par le workflow Runtime | 4.7A-R1 |
| motif d'audit | `AdministrationAudit` | preuve fermée de présence | transport du texte dans le workflow ou les événements | 4.7A-R1 |
| décision four-eyes | `AdministrationAudit` | décision explicite et versionnée | recalcul par repository ou orchestrateur | 4.7A-R1 |
| registre PostgreSQL historique | `AdministrationAudit` | source auditée pour une stratégie de coexistence | modification opportuniste de la migration historique | 4.7B-R1 |
| PostgreSQL Runtime | Runtime certifié | PDO et transaction génériques | connexion parallèle | 4.7C |
| Public Projection Delivery | fondation générique | transport opaque et consommation fermée | contrat spécialisé concurrent | 4.7F–4.7H |
| Outbox multi-owner | fondation générique | réutilisation après audit de l'owner | duplication de Writer/Reader | 4.7H-R1 |
| Runtime Health | Runtime certifié | inspection structurelle des bindings | requête ou décision pendant l'inspection | 4.7C |

Les capacités 4.1 à 4.6 ne sont pas des sources de décision pour ce lifecycle. Elles ne peuvent être appelées ni modifiées.
