# Place Lifecycle Persistence Specification

## Autorité

La Persistence Foundation stocke mécaniquement une transition déjà décidée.
Elle ne rappelle jamais le Workflow et ne possède aucune règle métier.

Selon l'amendement 4.8A-R2, elle possède exclusivement les décisions de
concurrence :

- `SourceVersionConflict`;
- `TargetVersionConflict`.

## Journal

`geography.place_lifecycle_transitions` est append-only. Chaque lieu est
enrôlé avec son état et sa version propriétaire, puis chaque transition ajoute
une ligne à la version suivante.

Une transition conserve intégralement `PlaceMergeContextV1` afin de garantir
l'idempotence et l'inspection future sans reconstruction.

## Concurrence

La source et la cible sont verrouillées dans l'ordre lexicographique de leurs
identités pour une fusion. `Enable` et `Disable` ne verrouillent et ne
contrôlent que la source : la concurrence cible n'appartient qu'à `Merge`.
Au commit d'une fusion :

- la version durable source doit égaler la version source attendue;
- la version durable cible doit égaler la version cible observée;
- l'état durable source doit égaler l'état de départ de la transition.

Les deux conflits de version sont distincts et fermés. Aucun commit partiel
n'est possible.

## Idempotence

La répétition byte-for-byte d'une transition déjà appendue retourne
`AlreadyApplied`. Une autre preuve visant la même version résultante retourne
`SourceVersionConflict`. L'identité d'intention est unique par source.

## Hors périmètre

Aucun Runtime, binding Laravel, Orchestration, Event, Transport, Routing,
Outbox, HTTP, Consumer ou Worker n'est introduit.
