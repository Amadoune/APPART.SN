# Phase 5.1H — Publication Recovery Policy

Le reader sélectionne dans l'ordre stable `(available_at, message_id, destination)` au moyen de `FOR UPDATE SKIP LOCKED`.

- un claim dure 30 secondes ;
- un claim expiré redevient sélectionnable ;
- chaque claim incrémente `attempts` ;
- un retry n'est sélectionnable qu'à `available_at` ;
- `markDelivered` exige l'owner du claim ;
- une quarantaine est terminale ;
- une erreur de worker ne supprime jamais le message.

La politique retry/replay 5.1G décide de la prochaine action ; l'Outbox 5.1H en persiste le résultat sans modifier cette politique.
