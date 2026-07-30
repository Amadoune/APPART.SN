# Place Lifecycle Event Routing — Spécification

## Chemin certifiable

```text
PlaceLifecycleTransportEnvelope
→ validation de cohérence
→ DurablePlaceLifecycleEventRouter
→ PlaceLifecycleInboxStore
→ Inbox PostgreSQL geography
```

## Destination

La destination unique est :

```text
geography.place_lifecycle_event_inbox
```

Le routeur ne publie rien à distance et ne choisit aucune autre destination.

## Idempotence et concurrence

`message_id` est unique. Chaque écriture acquiert un verrou transactionnel
PostgreSQL dérivé de `messageId`.

- première écriture exacte : `Stored`;
- rejeu byte-for-byte identique : `AlreadyStored`;
- même identité avec contenu divergent : `Rejected`.

`Stored` et `AlreadyStored` deviennent tous deux `Routed`.

## Atomicité

Le repository rejoint une transaction externe existante. Sinon, il ouvre,
valide ou annule sa propre transaction. Un rollback externe supprime
intégralement l'écriture Inbox.

## Frontière

Aucun Outbox, Consumer, Worker, HTTP, projection ou logique métier n'est
introduit. Aucun binding Runtime n'est ajouté.
