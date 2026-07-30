# Phase 4.7G — Administrative Action Lifecycle Inbox PostgreSQL Specification

## Stockage

Table append-only :

```text
administration_audit.administrative_action_lifecycle_event_inbox
```

La table conserve :

* `messageId` unique ;
* type et version de transport ;
* événement canonique byte-for-byte ;
* source et `eventId` métier ;
* checksum du payload ;
* enveloppe de transport byte-for-byte ;
* état technique initial `pending` ;
* compteur initial nul.

## Idempotence et concurrence

Un verrou advisory transactionnel est pris à partir de `messageId`.

* premier stockage exact : `Stored` ;
* rejeu strictement identique : `AlreadyStored` ;
* même `messageId` avec une divergence : `Rejected` ;
* aucune donnée existante n'est écrasée.

Les transactions externes sont réutilisées. Une transaction locale est ouverte uniquement lorsque nécessaire.
