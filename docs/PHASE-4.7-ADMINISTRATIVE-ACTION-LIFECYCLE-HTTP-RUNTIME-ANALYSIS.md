# Phase 4.7 — Administrative Action Lifecycle HTTP Runtime Analysis

## Objet

Le Sprint 4.7J expose une frontière HTTP unique vers l'intégrateur atomique
4.7I. La couche HTTP valide le protocole, construit mécaniquement les contrats
certifiés et traduit le résultat fermé. Elle ne décide aucune transition.

## Endpoint

```text
POST /api/administrative-action-lifecycles/{actionId}/transitions
```

`actionId` est un UUID imposé par la route.

## Chaîne

```text
HTTP JSON
→ AdministrativeActionLifecycleTransitionRequest
→ AdministrativeActionLifecycleAtomicEventRequest
→ AdministrativeActionLifecycleAtomicEventOrchestrator
→ AdministrativeActionLifecycleHttpResultMapper
→ HTTP JSON
```

## Frontières

Le contrôleur n'accède jamais au Workflow, aux stores, à l'inspecteur, à
PostgreSQL, au Writer Outbox ou au routeur. Il n'ouvre aucune transaction et ne
produit aucun événement directement.

Le FormRequest ne recalcule pas la règle four-eyes : il transporte les
identités et la disposition explicites, vérifie leur cohérence de protocole,
puis appelle les factories contractuelles V1.

## Runtime

La route et l'adaptateur n'ajoutent aucune capacité Runtime Health. L'intégrateur
atomique demeure le singleton certifié en 4.7I. Runtime Health reste à 50
capacités.
