# Public Projection Worker — analyse

## Frontières auditées

ADR-1009 attribue au relay l'orchestration après commit, tandis que les modules sources restent propriétaires des faits et de leurs Outbox locales. Le Worker ne possède ni règle métier, ni reconstruction, ni stockage. Le Reader sélectionne un lot borné et causalement trié ; le ClaimManager arbitre les leases ; le Writer rend chaque issue durable ; le Consumer produit uniquement un résultat normatif.

Le Consumer Public Projection réel, l'appel à `PublicListingProjectionUpdater`, la résolution des sources, les bindings Laravel, la supervision permanente, la réconciliation et les outils d'exploitation restent dans les étapes ultérieures.

## Décisions

- un `runOnce` lit au plus la taille configurée et termine toujours ;
- une seule tête par `(sourceModule, aggregateType, aggregateId, consumerId)` est activée par passage ;
- les Aggregates distincts n'ont aucun ordre global et peuvent être claimés indépendamment ;
- le registre est construit explicitement avec consumerId, eventType, payloadVersion et instance ;
- les dix résultats Consumer sont interprétés exhaustivement sans branche par défaut ;
- Consumed, AlreadyConsumed et RejectedObsolete deviennent Delivered ;
- les deux blocages utilisent les classifications Outbox dédiées ;
- incompatibilités, divergence et échec permanent sont quarantinés ;
- RetryableFailure consulte exclusivement `PublicProjectionOutboxRetryPolicy`, puis schedule ou quarantine AttemptsExhausted ;
- une exception Consumer est réduite à RetryableFailure, sans stack trace ni payload ;
- la garantie est at-least-once avec idempotence du Consumer, jamais exactly-once.

## Lacune révélée par le crash recovery

Le ClaimManager PostgreSQL savait reprendre une lease expirée, mais le Reader excluait toutes les lignes Claimed. La sélection claimable inclut désormais uniquement les claims dont `claimed_until <= clock_timestamp()`. Une lease active reste invisible ; une lease expirée redevient récupérable sans mutation de schéma.
