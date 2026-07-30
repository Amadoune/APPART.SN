# Phase 4.8L — Place Lifecycle HTTP Runtime

## Endpoint unique

```text
POST /api/place-lifecycles/{placeId}/transitions
```

Le paramètre `placeId` est contraint au format UUID.

## Chaîne

```text
PlaceLifecycleTransitionRequest
→ validation stricte et fermée
→ PlaceLifecycleAtomicEventRequest
→ PlaceLifecycleAtomicEventOrchestrator
→ PlaceLifecycleHttpResultMapper
→ JsonResponse
```

Le contrôleur n'appelle jamais le Workflow, le store, le journal, le catalogue
événementiel, le Writer ou la transaction.

## Entrée fermée

Les quinze champs autorisés sont :

- `action`, `currentState`, `contextVersion`;
- `expectedSourceVersion`;
- `targetId`, `observedTargetVersion`, `observedTargetState`;
- `observedSourceType`, `observedTargetType`;
- `observedSourceCountry`, `observedTargetCountry`;
- `actorId`, `occurredAt`, `recordedAt`, `intentId`.

Tout champ supplémentaire est rejeté. Aucune identité, version, cible, preuve
de type, pays, état ou instant n'est reconstruite implicitement.

## Matrice HTTP

| Résultat | HTTP |
|---|---:|
| `Applied`, `AlreadyApplied` | 200 |
| `InspectionMissing` | 404 |
| `ContextDivergence`, `ReplayConflict` | 409 |
| `SourceVersionConflict`, `TargetVersionConflict`, `StateConflict` | 409 |
| `WorkflowRefused`, `TransitionRejected` | 422 |
| `InspectionCorrupted` | 503 |
| `AtomicIntegrationFailure` | 503 |

Le mapper ne contient aucun `default`.

## Frontière

Aucun événement, Transport, Routing, Worker ou orchestrateur parallèle n'est
créé. Les migrations 038, 039 et 040 et les contrats 4.8F à 4.8K restent
inchangés. Runtime Health demeure **Healthy — 55 capacités**.
