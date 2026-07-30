# Public Projection Worker Foundation

## Rôle et ownership

`PublicProjectionDeliveryWorker` est un orchestrateur Application spécialisé. Il livre après commit les messages déjà durables. Il dépend seulement des ports Reader, ClaimManager, Writer et RetryPolicy, d'un registre explicite, d'une horloge, d'un workerId, d'une taille de lot et d'une durée de lease. Il ne dépend ni de Laravel, PDO, SQL, Repository ou Aggregate.

## Passage borné et registre

`runOnce(consumerId)` demande un lot borné, retient au plus la tête causale de chaque Aggregate, tente une lease et livre uniquement les claims acquis. Chaque message produit un outcome immutable. Le registre fermé associe consumerId, eventType, payloadVersion et Consumer ; les doublons de configuration sont refusés et aucune découverte automatique ou fallback n'existe.

## Ordre, claims et concurrence

Le Reader trie par module, type et identifiant d'Aggregate, puis `(aggregateVersion, eventIndex)`. Le Worker n'active jamais deux messages du même flux dans un passage. Un autre Aggregate peut avancer sans verrou global. Les claims PostgreSQL restent l'arbitre entre workers et connexions. Une lease active ne peut être volée ; une lease expirée est sélectionnée et reprise.

## Résultats, retry et quarantaine

Consumed, AlreadyConsumed et RejectedObsolete sont acquittés Delivered. SequenceGap et SourceReadiness deviennent les états bloqués distincts. UnsupportedEventType, UnsupportedPayloadVersion, DivergentPayload et PermanentFailure vont en quarantaine avec raison et code minimaux. RetryableFailure consulte la policy ; une décision autorisée est planifiée durablement, sinon AttemptsExhausted est quarantiné. Aucun retry ne boucle dans le même run.

## Crash recovery et redelivery

Avant l'appel Consumer, l'expiration rend le message claimable. Après effet mais avant Delivered, la redelivery attend un Consumer idempotent qui répond AlreadyConsumed, puis le Worker acquitte Delivered. Retry et quarantaine étant écrits par l'Outbox, un crash ultérieur ne les perd pas. La garantie est at-least-once plus idempotence durable, sans promesse exactly-once.

## Rapport et limites

Le résultat de lot expose le nombre demandé, claimé, l'âge maximal des messages observés en secondes et la liste des outcomes : delivered, alreadyConsumed, obsolete, retryScheduled, blocages, quarantaine, conflit de claim, lease expirée, unsupported, défaillance technique ou report causal. Le lag utilise `recordedAt` uniquement comme mesure technique et jamais comme causalité. Le rapport ne contient aucun payload ou secret.

Aucun daemon, commande Artisan, scheduler, Job, Queue, binding Laravel, Controller, route, vue ou appel réel à l'Updater n'est créé. La Retry and Quarantine opérationnelle, le Consumer réel, la réconciliation et le runtime restent réservés aux sprints suivants.
