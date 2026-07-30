# Phase 5.3F — Orchestration & Four-Eyes Analysis

## Conclusion

`NO GO PROPOSÉ`

L'orchestration des mutations de dossier est compatible avec les garanties
transactionnelles de 5.3D. En revanche, l'opération
`ClaimModerationQueueItemV1` ne peut pas respecter le contrat d'idempotence V1
sans modifier deux fondations désormais gelées.

## État réel

Le contrat documentaire certifié impose à `ClaimModerationQueueItemV1` :

- `intentId` ;
- un checksum couvrant toutes les données sémantiques ;
- `AlreadyApplied` pour le même intent et le même checksum ;
- `DivergentIntent` pour le même intent et un checksum différent ;
- aucune réinterprétation d'un intent terminal.

La frontière Queue certifiée expose seulement :

```text
claim(
    queueItemId,
    leaseId,
    claimOwnerId,
    leaseExpiresAt,
    claimedAt
)
```

Elle ne reçoit ni `intentId`, ni checksum.

Le schéma 063 persiste sur `queue_items` :

- l'identité de l'item et du dossier ;
- le lease ;
- le claim owner ;
- l'expiration ;
- la version source.

Il ne possède aucun journal d'intents Queue. Le journal
`moderation_reports.case_intents` appartient aux mutations du dossier et n'est
pas accessible par la frontière Queue.

## Incompatibilité démontrée

À données persistées identiques, la Queue ne peut pas distinguer :

1. la répétition du même intent avec le même checksum ;
2. la répétition du même intent avec un checksum différent ;
3. un intent concurrent distinct portant un autre lease.

Assimiler ces situations à `AlreadyClaimed` ou `LeaseConflict` violerait le
catalogue fermé de 5.3B. Un cache en mémoire ne serait ni durable, ni atomique,
ni sûr sous concurrence.

## Impacts de la correction nécessaire

Une correction conforme exigerait au minimum :

- une persistence owner-local durable des intents Queue ;
- la transmission de `intentId` et du checksum jusqu'au store ;
- une convergence atomique sous advisory lock ;
- une migration additive postérieure à 063 ;
- la recertification d'impact des frontières Queue de 5.3D et 5.3E.

Ces changements sont interdits pendant 5.3F, car 5.3D et 5.3E sont gelées et
aucune nouvelle migration n'est autorisée.

## Compatibilité des autres opérations

Les cinq mutations du dossier peuvent s'appuyer sans extension sur
`ModerationCaseStore` :

- journal d'intents permanent ;
- comparaison du checksum avant la version ;
- optimistic locking ;
- advisory lock transactionnel ;
- transaction locale et savepoint ;
- révisions append-only ;
- supersession explicite.

Cette compatibilité partielle ne suffit pas à certifier le jalon, dont les six
Commands sont obligatoires.

## Amendement identifié

```text
A-5.3-MODERATION-QUEUE-IDEMPOTENCE-01
IDENTIFIÉ
NON OUVERT
```

L'amendement devrait auditer et autoriser explicitement l'extension additive de
la Persistence Queue et de sa frontière Runtime. Il n'est pas ouvert
automatiquement par ce dossier.

## Garanties préservées

Aucune implémentation 5.3F n'est conservée. Aucun changement n'est apporté à :

- la migration 063 ;
- la Persistence 5.3D ;
- le Runtime et la Queue 5.3E ;
- Runtime Health ;
- HTTP, Event, Delivery ou Outbox ;
- une frontière externe ou l'un des six amendements 5.3C.
