# Phase 4.9A — Matrice des responsabilités

| Décision / donnée | Owner unique | Non propriétaire |
|---|---|---|
| Définition de `Active` / `Suspended` | Responsable Identité et Accès | Runtime, HTTP, consommateurs |
| Autorité de suspension/réactivation | Responsable Identité et Accès | Workflow, persistance |
| Validité état/action | Workflow futur | Orchestration, HTTP |
| Existence courante du compte | Persistance future | Workflow |
| Conflit de version | Persistance future | Workflow, Inspection |
| Historique de rejeu | Inspection future | Workflow |
| Classification du rejeu | classificateur futur, après contrat certifié | Orchestration |
| Séquencement | Orchestration future | Workflow |
| Effet sur rôles | Owner Role Assignment; aucune révocation implicite | Account Status Workflow |
| Effet sur sessions | Owner Authentication/Session; aucune invalidation implicite | Account Status Workflow |
| Credentials | Owner Credential | Account Status Workflow |
| Vérifications | Owner Verification | Account Status Workflow |
| Consentements | Owner Consent | Account Status Workflow |
| Publication d'un fait | intégration atomique future | Workflow seul |
| Projection aval | owner de chaque projection | `IdentityAccess` ne réécrit pas la projection |
| Composition | Runtime futur | domaine |

## Règle normative

Chaque ligne possède un seul owner. Une couche peut consommer un résultat
fermé d'une autre couche, mais ne peut ni le recalculer ni le substituer.
Les effets sur rôles et sessions sont certifiés par le registre 4.9A. La
répartition technique détaillée de Workflow, Inspection, Orchestration et
Persistance est certifiée par `4.9A-R1`.
