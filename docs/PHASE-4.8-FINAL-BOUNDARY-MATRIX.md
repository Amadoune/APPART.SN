# Phase 4.8 — Matrice finale des frontières

| Frontière | Propriétaire unique | Interdictions consolidées |
|---|---|---|
| Qualification cible | préparation du `PlaceMergeContextV1` | aucune reconstruction par Workflow |
| Rejeu durable | `PlaceMergeContextInspector` | aucune décision de cible |
| Classification du rejeu | `PlaceMergeReplayClassifier` | aucune écriture |
| Décision métier | `PlaceLifecycleWorkflow` | aucun SQL, Runtime ou Event |
| Séquencement | `PlaceLifecycleOrchestrator` | aucune redécision |
| Journal et contexte | `PlaceLifecycleWorkflowStore` | aucune publication |
| Fait métier | `PlaceLifecycleEventCatalog` | aucune persistance |
| Transport | contrats 4.8G | aucune décision métier |
| Outbox | composants PublicProjection génériques | aucune spécialisation Place |
| Atomicité | `PlaceLifecycleAtomicEventOrchestrator` et transaction générique | aucun commit intermédiaire |
| Delivery | Worker générique | aucune reconstruction du lifecycle |
| Routage | Router 4.8H | aucune redécision Workflow |
| Inbox | persistance 039 | aucune Outbox parallèle |
| HTTP | Request, Controller et Mapper 4.8L | aucun accès direct aux couches internes |

Aucune responsabilité ne possède deux propriétaires et aucune couche ne dépend
d'une information absente de ses entrées certifiées.
