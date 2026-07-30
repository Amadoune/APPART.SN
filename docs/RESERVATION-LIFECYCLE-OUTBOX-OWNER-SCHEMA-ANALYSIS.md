# Reservation Lifecycle Outbox Owner Schema Analysis

## Contexte et décision

Le pipeline Delivery générique répartit ses messages dans le schéma PostgreSQL propriétaire du module source. Avant 4.3H-R1, `ReservationLifecycle` était un module source valide sans résolution d'owner Outbox. Il possède désormais exclusivement le schéma `reservation_lifecycle`.

Cette relation est déclarée dans le résolveur central utilisé dans les deux sens : module vers schéma pour le Writer, schéma vers module pour le Reader. La migration historique 005 reste gelée ; la migration additive 021 reproduit seulement les quatre structures génériques dans le nouvel owner.

## Invariants

- un message `ReservationLifecycle` est écrit uniquement dans `reservation_lifecycle` ;
- chaque branche lue impose que `source_module` corresponde à l'owner du schéma ;
- une copie placée dans un mauvais owner n'est jamais exposée ;
- les six owners sont parcourus dans un ordre explicite et stable ;
- l'identité reste locale au schéma propriétaire ;
- aucun catalogue, Consumer, Worker ou producteur Reservation n'est ajouté.

Le blocage de schéma est levé sans anticiper la compatibilité événementielle 4.3H.
