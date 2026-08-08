# Phase 5.8B — Reliability & Operations — Authority Analysis

`ReliabilityOperations` reste l'unique autorité de ses états owner-scoped. Il ne reçoit aucune autorité métier sur les systèmes observés.

| Élément | Autorité |
|---|---|
| état persistant d'un stream | ReliabilityOperationsOwnerSource |
| validation du catalogue interne | ReliabilityOperationsRevisionState |
| exposition publique V1 | contrat Reader V1 dédié |
| réduction future | Owner Reader mécanique dédié |
| disponibilité technique | Runtime, explicitement hors chaîne Reader |
| décision métier d'un domaine observé | owner métier concerné, jamais ReliabilityOperations |

Un futur Owner Reader ne pourra ni consulter Runtime, ni interroger PostgreSQL, ni reconstruire un état depuis des métriques, logs, traces ou autres sources. Il devra réduire un seul `ReadResult`, sans agrégation et sans fallback.
