# Property Lifecycle Persistence Analysis

## Modèle

La persistance matérialise les décisions 4.2A dans un journal append-only. La clé `(property_id, version)` ordonne le cycle métier sans timestamp. Une ligne d'initialisation porte la version 1 ; les suivantes portent la transition complète certifiée.

## Responsabilités

Le workflow reste seul propriétaire des décisions. Le repository contrôle uniquement l'existence, la version attendue, la continuité de l'état source, l'idempotence et l'intégrité technique. Il ne contient aucune matrice de transitions.

PostgreSQL refuse les formes, états, actions et triplets étrangers au contrat. Cette contrainte protège les données contre une écriture défectueuse sans choisir une transition.

## Concurrence

Un verrou transactionnel consultatif dérivé de `property_id`, complété par la clé primaire, sérialise les écritures concurrentes d'un même Property. Des Properties distincts restent indépendants. Le repository rejoint une transaction externe lorsqu'elle existe et n'en crée aucune abstraction métier supplémentaire.
