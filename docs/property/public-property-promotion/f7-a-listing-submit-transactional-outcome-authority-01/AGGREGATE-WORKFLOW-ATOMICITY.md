# Atomicité Aggregate / Workflow

## Invariant obligatoire

Après le retour de `submit()`, un échec de la tentative ne peut jamais laisser :

`Listing Aggregate = Submitted` et `Publication Workflow != Submitted-compatible`.

Les seules sorties persistantes autorisées sont :

- succès : Aggregate Submitted, Workflow Submitted, public facts/outbox/queue locaux convergés ;
- échec : Aggregate et Workflow dans leur paire pré-Submit, aucun handoff de succès de la tentative.

`AlreadyApplied` autorise un commit uniquement si la transition retournée est présente et si la paire finale est cohérente. Le savepoint interne de l’orchestrateur ne remplace pas le verdict de la transaction Listing englobante.
