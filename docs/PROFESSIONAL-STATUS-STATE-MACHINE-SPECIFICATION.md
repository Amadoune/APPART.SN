# Professional Status State Machine Specification

## Ensembles fermés

- états : `Active`, `Suspended` ;
- actions exécutables : `Suspend`, `Reactivate` ;
- sentinelle de transport contractuelle : `Unknown` ;
- décisions : `Allowed`, `Denied` ;
- diagnostics : `IncompatibleState`, `UnknownAction`.

Une décision `Allowed` contient exactement une transition et aucun diagnostic. Une décision `Denied` contient exactement un diagnostic et aucune transition.

Il n'existe ni état `Unregistered`, ni état terminal, ni branche `default`.
