# Phase 5.1G — Event Transport V1

`IdentityAccessDeliveryMessageV1` transporte le contrat canonique avec :

- message ID déterministe ;
- message type égal au type métier ;
- transport version 1 ;
- source `IdentityAccess` ;
- event ID et checksum du payload.

La restauration reconstruit le contrat, recalcule event ID et checksums, compare la forme exacte et exige une ré-encodage byte-identique. Toute altération ou clé inconnue est rejetée fail-closed.

Ce transport ne dépend ni de Laravel, ni de PDO, ni d'HTTP, ni de l'Outbox.
