# PostgreSQL Outbox Foundation

## Statut

**NO GO de certification — identifiants PostgreSQL de test manquants.** L'implémentation et les tests sont présents, mais la preuve réelle obligatoire n'a pas pu être exécutée.

## Schémas et tables

Chaque module propriétaire possède les mêmes tables dans son schéma : ListingLifecycle, RealEstateCatalog, Media, SearchDiscovery et ContentSeo.

- `public_projection_outbox_messages` : enveloppe immutable, clé idempotente unique, causalité, payload JSONB et checksum ;
- `public_projection_outbox_deliveries` : état par message/consommateur, attempts, lease, retry, delivery et quarantaine ;
- `public_projection_outbox_cursors` : progression/high-watermark ;
- `public_projection_outbox_replays` : demandes bornées de replay.

La séparation message/delivery préserve l'unicité globale de l'idempotency key tout en autorisant plusieurs consommateurs. Aucune FK vers un Aggregate n'est créée : l'Outbox doit survivre à un retrait métier et reste locale par ownership, pas par cycle de vie.

## Contraintes et index

- PK et UNIQUE sur messageId/idempotencyKey ;
- unicité causale Aggregate/type/version/index/eventType/payloadVersion ;
- versions et index strictement positifs ;
- JSONB obligatoirement objet ;
- cohérence claim/lease, retry et quarantaine par CHECK ;
- PK delivery `(message_id, consumer_id)` ;
- index claim `(consumer_id,status,available_at,message_id)` pour les lots disponibles ;
- index causal `(aggregate_type,aggregate_id,aggregate_version,event_index)` pour ordre, replay et diagnostic.

## Mapping

Le mapper sérialise uniquement les cinq DTOs Delivery certifiés. À la lecture il recalcule idempotency key et checksum, refuse tout champ supplémentaire et ne contient aucune règle métier.

## Transactions

L'enveloppe spécialisée possède l'unique transaction. Le participant injecté dans les repositories existants interdit tout commit autonome. Repository et Writer partagent le même PDO. Une exception quelconque rollback Aggregate et Outbox ; aucun message n'est publié.

## Claim et concurrence

Le claim ciblé utilise un CTE avec `FOR UPDATE` puis un UPDATE conditionnel dans la même instruction. Une ligne déjà claimée et non expirée reste inaccessible ; une lease expirée est reprise et les attempts sont incrémentées. Cette stratégie offre l'exclusion par ligne sans verrou global. Un futur batch pourra sélectionner avec `FOR UPDATE SKIP LOCKED`, mais aucun Worker ou batch n'est créé ici.

## Reader, retry, quarantaine et cursor

Le Reader agrège les cinq schémas puis trie exclusivement par module, Aggregate, aggregateVersion et eventIndex. Writer, ClaimManager et CursorStore implémentent exactement les contrats 3.6C.2. Le retry calcule `available_at` à partir du backoff contractuel ; les blocages readiness/sequence n'entrent pas dans une boucle temporelle.

## Limites et préparation Worker Foundation

Aucun binding, Worker, Dispatcher, Relay ou appel Updater n'existe. Avant Worker Foundation, la suite PostgreSQL complète et la concurrence multiprocessus doivent être exécutées avec les credentials officiels. Geography et Public Media restent sans révision stable.
