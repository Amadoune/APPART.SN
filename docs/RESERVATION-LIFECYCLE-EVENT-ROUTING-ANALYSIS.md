# Reservation Lifecycle Event Routing Analysis

## Décision

Le sprint 4.3G introduit le premier routeur concret de Reservation Lifecycle. `DeterministicReservationLifecycleEventRouter` est l'unique implémentation de `ReservationLifecycleEventRouterPort`. Il dépend uniquement de `ReservationLifecycleInboxStore` et des contrats de transport 4.3F/4.3F-R1.

Le routeur vérifie la cohérence de l'enveloppe en reconstruisant sa forme technique attendue à partir du payload déjà certifié. Il ne recalcule aucune transition et ne consulte ni workflow ni Reservation. Une enveloppe incohérente est refusée avant tout appel au store.

## Persistance

`PostgreSqlReservationLifecycleInboxRepository` sérialise les écritures d'un même `messageId` par un verrou transactionnel consultatif, puis réalise une insertion atomique avec `ON CONFLICT (message_id) DO NOTHING`. Une insertion nouvelle produit `Stored`. Après conflit, tous les champs persistés sont comparés exactement : une ligne identique produit `AlreadyStored`, toute divergence produit `CorruptedEnvelope`.

Les erreurs PostgreSQL de données ou d'intégrité deviennent `CorruptedEnvelope`. Toute autre impossibilité de produire un résultat fiable devient `PersistenceCorrupted`. Aucune exception technique n'est exposée.

## Frontières

- Aucun binding Laravel ou ajout Runtime Health.
- Aucune Outbox, publication, consommation ou projection.
- Aucun timestamp, UUID aléatoire ou règle métier.
- Le contrat 4.3F reste inchangé et le payload est traité comme une chaîne opaque.
