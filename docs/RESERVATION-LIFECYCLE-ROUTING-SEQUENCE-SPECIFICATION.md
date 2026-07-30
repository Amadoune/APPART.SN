# Reservation Lifecycle Routing Sequence Specification

```text
ReservationLifecycleTransportEnvelope
  -> DeterministicReservationLifecycleEventRouter
     1. reconstruit l'enveloppe technique attendue
     2. compare messageId, type, version, métadonnées, checksum et octets
     3a. incohérence -> CorruptedEnvelope
     3b. cohérence -> ReservationLifecycleInboxStore::store
        -> verrou transactionnel déterministe par message_id
        -> INSERT ... ON CONFLICT (message_id) DO NOTHING
           4a. ligne insérée -> Stored
           4b. conflit -> lecture exacte par message_id
               5a. tous les champs identiques -> AlreadyStored
               5b. divergence -> CorruptedEnvelope
           4c. erreur non fiable -> PersistenceCorrupted
```

Le routeur appelle le store au maximum une fois. Une enveloppe refusée n'atteint jamais PostgreSQL. Le repository ne désérialise ni n'interprète `canonicalEvent`; il compare et persiste ses octets.

Aucune étape ne déclenche workflow, événement, Outbox, Worker, Consumer ou Projection.
