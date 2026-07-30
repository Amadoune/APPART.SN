# Phase 5.3H — Outbox Foundation

## Objet

La fondation fournit l'Outbox propriétaire complète de `ModerationReports`.
Elle persiste de manière fiable les événements V1 déjà certifiés, sans activer
de handoff externe.

## Ownership

| Élément | Owner |
|---|---|
| Message Outbox | ModerationReports |
| Destination Outbox | ModerationReports |
| Claim et lease | ModerationReports |
| Retry, replay et quarantaine | ModerationReports |
| Transaction atomique | ModerationReports |

## Modèle

`outbox_messages` conserve l'identité et le transport canonique immuable.
`outbox_deliveries` porte l'état technique propre à chaque destination
owner-local.

Les états fermés sont :

- `Pending` ;
- `Claimed` ;
- `Retry` ;
- `Delivered` ;
- `Quarantined`.

Le ledger Delivery 065 reste distinct et inchangé.

## Publication atomique

La séquence autorisée est :

```text
ModerationAtomicOperationV1
  ├─ mutation métier 5.3F
  └─ ModerationOutboxAppenderV1
       ├─ outbox_messages
       └─ outbox_deliveries
```

Un résultat métier non committable empêche l'append. Un append divergent ou
rejeté annule la mutation. Une transaction englobante utilise un savepoint et
conserve la propriété de son commit final.

## Idempotence et concurrence

- même `messageId` et même checksum : `AlreadyStored` ;
- collision sur `messageId` ou `eventId` avec checksum différent :
  `DivergentMessage` ;
- advisory lock sur `messageId` ;
- claims concurrents via `FOR UPDATE SKIP LOCKED` ;
- messages et destinations sans duplication.

## Lifecycle technique

- `claimNext` réserve une livraison pour trente secondes ;
- `release` la remet immédiatement à disposition ;
- `retry` programme une nouvelle disponibilité ;
- un lease expiré est automatiquement réclamable ;
- `markDelivered` clôt la tentative ;
- `quarantine` crée un état terminal technique ;
- `replay` remet explicitement une livraison délivrée ou quarantinée en attente.

## Hors périmètre

- Command Handoff ;
- Gateway ou Reader externe ;
- HTTP ;
- consumer externe ;
- émission vers un domaine externe ;
- consommation des amendements 5.3C ;
- modification de Runtime Health, 5.3F ou 5.3G.
