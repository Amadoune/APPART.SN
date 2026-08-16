# Resume Read Contract

Une composition Application read-only est nécessaire : `AuthoringDraftResumeReaderV1`.

Entrée minimale :

- AccountId issu de la session ;
- listingId sélectionné ;
- observedAt uniquement si une lecture dépend explicitement du temps.

Catalogue fermé retenu :

- `Available` ;
- `NotFoundOrForbidden` ;
- `Incomplete` ;
- `StateConflict` ;
- `Corrupted` ;
- `DependencyUnavailable`.

Le Reader compose Portfolio, Draft, Ownership, Property Authoring, Aggregate, Workflow, Geography Registry et Media Collection. Il n’accède ni à Promotion, ni Projection, ni Search. Il n’écrit aucun store.

Le résultat `Available` contient un snapshot immuable et un step dérivé. `NotFoundOrForbidden` reste volontairement fusionné conformément aux conventions HTTP Authoring existantes.
