# Phase 5.2C — HTTP Dependency Matrix

## Dépendances autorisées

| Source HTTP | Cible | Mode |
|---|---|---|
| auto-scope privé | `ProfessionalMandateResolverV1` | query publique V1 |
| disponibilité publique | `ProfessionalPublicStatusReaderV1` | query publique V1 |
| données Professional Profile | `ProfessionalProfileRuntimeV1` | façade Runtime certifiée |
| authentification | session IAM certifiée | AccountId uniquement |

## Dépendances interdites

| Cible | Interdiction |
|---|---|
| `ProfessionalRegistry` | Aggregate/persistence owner |
| `Professional`, `RepresentativeMandate`, `RepresentativeId` | internals Professional Core |
| `ProfessionalStatusWorkflowStore` | persistence F-05 |
| `ProfessionalStatusOrchestrator` | frontière de commande |
| événements F-05 ou mandat | reconstruction interdite |
| SQL / tables | lecture directe interdite |
| IAM Aggregate/stores | contournement de la session |

## Transactions

HTTP ne démarre aucune transaction cross-domain. Chaque command future est
déléguée à la Runtime certifiée après résolution read-only des deux frontières.
