# Phase 4.9B — Account Status Workflow Foundation

## Objet

La fondation implémente exclusivement la décision métier pure du
`Account Status Lifecycle`, conformément à 4.9A-R1.

## Contrats fermés

États :

```text
Active
Suspended
```

Actions :

```text
Suspend
Reactivate
```

Décisions :

```text
Applied
AlreadyInState
InvalidContext
```

L'état initial est `Active`.

## Entrée pure

```text
AccountStatusCurrentState
+ AccountStatusAction
+ AccountStatusContextV1
→ AccountStatusWorkflowResult
```

Le contexte V1 porte explicitement identité, état observé, versions attendue
et observée, action, acteur, instant métier et identité d'intention.

`InvalidContext` est limité à :

- métadonnée structurellement invalide;
- identité différente entre état courant et contexte;
- état observé différent;
- version observée différente;
- action différente.

Le Workflow ne compare pas `expectedVersion` à l'état durable. Cette
responsabilité reste à la Persistance.

## Pureté

La fondation ne contient ni Inspection, Persistance, Repository, Runtime,
Event, Transport, Routing, Outbox, HTTP, Worker, projection ou lecture
durable. Elle ne dépend pas de l'agrégat `Account`.

Elle ne produit jamais `AlreadyApplied`, `ReplayConflict`,
`InspectionCorrupted`, `AccountMissing`, `VersionConflict` ou
`PersistenceRejected`.

## Sous-domaines

Le Workflow ne lit ni ne modifie rôles, sessions, Credentials, Verification
ou Consent. Il ne produit aucun effet implicite sur ces sous-domaines.
