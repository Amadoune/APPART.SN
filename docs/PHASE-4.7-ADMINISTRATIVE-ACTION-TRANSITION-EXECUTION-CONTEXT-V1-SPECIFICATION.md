# Phase 4.7C-R1 — Transition Execution Context V1

Le contrat immuable contient exactement :

| Champ | Invariant |
|---|---|
| `contractVersion` | `V1` |
| `expectedVersion` | entier positif ou nul, explicite |
| `actor` | identité typée explicite |
| `occurredAt` | UTC explicite |
| `decisionIdentities` | forme fermée propre à l'action |
| `historicalReason` | motif historique typé explicite |
| `decisionContext` | instance V1 certifiée, jamais reconstruite |
| `checksum` | SHA-256 canonique de tous les champs |

Les fabriques sont fermées :

* `record` : aucun `ApprovalId`, aucun `DecisionId`, acteur égal à l'auteur certifié ;
* `approve` : `ApprovalId` et `DecisionId` obligatoires, acteur égal au décideur certifié ;
* `reject` : `DecisionId` obligatoire et aucun `ApprovalId`, acteur égal au décideur certifié.

Aucune horloge, identité, version ou valeur par défaut n'est fournie.
