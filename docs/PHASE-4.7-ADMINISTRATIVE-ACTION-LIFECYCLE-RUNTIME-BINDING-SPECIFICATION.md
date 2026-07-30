# Phase 4.7C — Runtime Binding Specification

Tous les composants sont enregistrés comme singletons paresseux dans `PublicProjectionRuntimeServiceProvider`.

| Contrat ou composant | Résolution | Portée |
|---|---|---|
| `AdministrativeActionLifecycleWorkflow` | lui-même | singleton |
| `AdministrativeActionLifecycleWorkflowMapper` | lui-même | singleton interne |
| `AdministrativeActionEnrollmentCanonicalizer` | lui-même | singleton interne |
| `PostgreSqlAdministrativeActionLifecycleAtomicPersistenceTransaction` | lui-même | singleton interne |
| `PostgreSqlAdministrativeActionLifecycleRepository` | lui-même | singleton |
| `AdministrativeActionLifecycleWorkflowStore` | alias du repository | même singleton |

Le constructeur du repository reçoit exclusivement les instances du conteneur. Aucun objet métier n'est créé et aucune opération de persistance n'est appelée lors de la résolution.
