# Property Lifecycle Runtime HTTP Sequence

1. Laravel sélectionne l'unique route POST et vérifie l'UUID.
2. `PropertyLifecycleTransitionHttpRequest` valide les quatre champs JSON.
3. Le Form Request construit les Value Objects et la requête 4.2I.
4. `PropertyLifecycleTransitionController` appelle une fois `PropertyLifecycleEventOrchestrator`.
5. Le contrôleur applique le mapping fermé du statut.
6. Laravel sérialise la réponse minimale.

Le bootstrap ne déclenche aucune transition. La transaction, l'événement et l'Outbox restent exclusivement derrière le port applicatif.
