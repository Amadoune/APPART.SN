# F2 — Implementation Evidence

## Chaînes matérialisées

| Opération | Composition | Résultat |
|---|---|---|
| Login | Resolver → Verifier → SecretAuthority → Store → Orchestrator | Session réelle et cookie opaque |
| InspectSession | cookie → SessionId → Store → Reduction | contexte `AccountId + SessionId` |
| Rotation | contexte → relecture → optimistic lock → nouveau secret/session | ancien cookie invalidé |
| Logout | contexte → relecture → optimistic lock → révocation | session refusée, replay idempotent |

## Anti-énumération

Une identité absente et un credential incorrect convergent vers le même `IdentityAccessHttpStatus::GenericFailure`. Le Controller conserve la réponse unique `authentication_failed` avec HTTP 401 et `Cache-Control: no-store`.

## Concurrence et replay

- l'orchestrateur prend l'advisory lock transactionnel owner-scoped Account/Session ;
- le store conserve son lock par row et son expected version ;
- six Login successifs convergent vers exactement cinq sessions actives ;
- la plus ancienne est révoquée mécaniquement ;
- Rotation ne possède qu'un gagnant par version ;
- Logout rejoué avec le même intent retourne un succès sans seconde mutation.

## Frontières préservées

- aucun SQL dans Runtime, Controller ou Middleware ;
- aucune cryptographie dans le Runtime ;
- aucune modification de la Security Policy ou des autorités F1 ;
- aucun changement de route, cookie ou payload public ;
- aucune dépendance Property, Media, Search ou Projection ;
- aucune migration F2.

## Binding

Le Provider compose les autorités et stores en singletons lazy. Après les validations terminales, l'alias public pointe sur `DeterministicIdentityAccessHttpRuntime`. `FailClosedIdentityAccessHttpRuntime` demeure disponible comme type historique, mais n'est plus le binding actif.
