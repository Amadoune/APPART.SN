# Ownership Decision

## Owner structurel

`ListingLifecycle` possède `ExpirationDate`, la transition `Published → Expired`, l'historique et l'événement `ListingPublished`. Aucun autre domaine ne peut acquérir l'autorité de calculer cette date.

## Autorité métier

`NOT_ESTABLISHED`.

La documentation affirme que la date doit être connue dès publication, mais classe explicitement parmi les arbitrages ouverts :

- la durée standard de publication ;
- la fenêtre et les règles de renouvellement ;
- l'effet d'une suspension sur la durée ;
- l'éventuel effet d'un modèle commercial.

Par conséquent, Listing Lifecycle reste le seul owner recevable, mais une décision métier explicite est requise avant de créer une policy ou une configuration.
