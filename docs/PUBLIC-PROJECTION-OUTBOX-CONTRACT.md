# Public Projection Outbox Contract

## Rôle et ownership

Le contrat Outbox décrit le journal logique entre un `PublicProjectionDeliveryMessage` déjà validé et sa livraison à un consommateur. Le module source possédera le futur stockage local ; ce contrat possède uniquement les états, intentions et résultats. Le Relay futur fournira horloge, concurrence, exécution et observabilité.

Le message conserve identité, causalité, payload et dates informatives. L'Outbox conserve par couple message/consommateur : statut, tentatives, claim, lease, retry et éventuelle quarantaine. Aucun champ SQL ou transactionnel n'apparaît dans le Record.

## Outbox Record

`PublicProjectionOutboxRecord` est immutable. Un nouveau message commence `Pending`, tentative 0, sans lease. Un Record `Claimed` exige exactement une lease active. `RetryScheduled` exige une décision Retry. `Quarantined` exige un QuarantineRecord. Le statut Delivery existant reste la source normative des transitions conceptuelles.

## Writer

`PublicProjectionOutboxWriter` expose uniquement :

- `append` ;
- `markDelivered` ;
- `scheduleRetry` ;
- `quarantine` ;
- `releaseClaim`.

Les résultats distinguent Applied, AlreadyApplied, DivergentMessage, InvalidTransition, ClaimMismatch et NotFound. Aucune API save/put/update générique n'existe.

## Reader

`PublicProjectionOutboxReader` lit les entrées claimables, bloquées, retryables, quarantinées ou appartenant à un Aggregate et un consommateur précis. Le tri contractuel est causal par Aggregate/version/index. Le Reader ne claim pas implicitement.

## Claim et lease

`PublicProjectionOutboxClaimManager` décrit claim, expiration et abandon. Une lease immutable contient ownerId, claimedAt et expiresAt ; l'horloge est fournie par l'appelant. Le contrat distingue Claimed, AlreadyClaimed, LeaseExpired, Released, NothingToClaim et Abandoned, ainsi que les résultats de blocage.

Le Fake démontre qu'un claim non expiré ne peut être volé, qu'un bail expiré peut être repris et que chaque reprise incrémente les tentatives. Aucun verrou ou calcul runtime n'est défini.

## Retry

Les contrats spécialisés séparent :

- classification Transient, Permanent, SourceNotReady ou SequenceGap ;
- backoff exprimé comme durée conceptuelle en secondes ;
- décision indiquant si un nouveau passage est autorisé ;
- policy abstraite, sans algorithme temporel.

Transient mène à RetryScheduled. SourceNotReady et SequenceGap mènent aux statuts bloqués correspondants sans boucle de retry implicite.

## Quarantaine

Les raisons fermées sont PermanentFailure, DivergentPayload, UnsupportedPayloadVersion, UnsupportedEventType, AttemptsExhausted et DurableBlockage. Le record conserve messageId, consommateur, raison, code d'erreur minimal et nombre de tentatives. Il ne conserve ni exception, stack trace, payload dupliqué ou secret.

## Cursor, high-watermark et replay

Le cursor est identifié par consommateur, module, type et identité d'Aggregate. Il conserve progression, high-watermark et dernier message. `PublicProjectionOutboxReplayRequest` définit une borne exclusive de départ facultative et une borne inclusive de fin. Le Fake Cursor Store démontre progression et programmation de replay sans stockage réel.

Le replay redélivre les mêmes messageId/idempotency keys ; il ne crée pas de nouvelle causalité et ne constitue pas un rebuild de projection.

## Fakes et invariants

Les quatre Fakes restent sous tests : Writer, Reader, ClaimManager et CursorStore. Ils partagent un état mémoire uniquement dans le harness. Ils démontrent append idempotent, payload divergent, claim, expiration, release, retry, blocages, quarantaine, ordre et replay.

## Limites

Aucun stockage, SQL, schéma, transaction, claim concurrent réel, horloge système, backoff calculé, Worker, Relay, Dispatcher ou Laravel n'existe. Le prochain PostgreSQL Outbox Foundation devra implémenter ces ports, prouver les contraintes et le rollback atomique, sans modifier le DeliveryMessage ni rouvrir ADR-1009.
