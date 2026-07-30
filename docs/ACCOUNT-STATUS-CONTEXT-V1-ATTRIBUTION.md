# Account Status — Future Context V1 Attribution

## Statut

Ce document attribue les données du futur contexte. Il ne crée ni classe,
interface, Value Object ni contrat technique.

## Données pressenties

| Donnée | Producteur autorisé | Consommateur autorisé | Rôle |
|---|---|---|---|
| `accountId` | frontière de requête validée | toutes les couches selon besoin | identité source |
| `currentState` | Persistance | Workflow | état observé |
| `expectedVersion` | requête/intention validée | Persistance | concurrence demandée |
| `observedVersion` | Persistance | contexte et Persistance | preuve observée |
| `action` | requête/intention validée | Inspection, Workflow | identité et décision |
| `actorId` | frontière d'autorisation future | contexte/audit futur | acteur explicite |
| `occurredAt` | source de temps métier future | Workflow/Persistance | ordre temporel |
| `intentId` | requête/intention validée | Inspection/Persistance | idempotence |
| `contextVersion = 1` | définition contractuelle future | Inspection/Persistance | version de forme |

## Invariants d'attribution

1. Le contexte est futur, immuable, explicite et indépendant du Runtime.
2. Le Workflow ne complète ni ne corrige une donnée manquante.
3. L'Orchestration assemble uniquement des valeurs produites par leurs owners;
   elle ne les recalcule pas.
4. Inspection compare l'identité complète mais ne modifie aucune donnée.
5. Persistance reste seule autorité sur état/version durables.
6. `actorId` ne transfère pas l'autorisation au Workflow.
7. Aucune donnée de rôle, session, Credential, Verification ou Consent
   n'appartient au contexte.

La forme syntaxique, les Value Objects et les résultats de construction ne
seront autorisés qu'après certification de 4.9A-R1.
