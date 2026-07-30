# Account Status — Delivery Consumption

Le Consumer reçoit exclusivement un `AccountStatusDeliveryMessage` et la
destination logique déjà sélectionnée. Il ne route pas.

```text
message + identity_access.account_status.lifecycle_facts
→ validation destination
→ reconstruction canonique
→ conservation messageId/eventId/payload/checksum
→ Consumed(fact)
```

Résultats fermés :

```text
Consumed
Rejected(
  UnsupportedMessage
  CorruptedMessage
  UnsupportedDestination
)
```

Le fait consommé restitue le contrat Event V1 et la même instance du message.
La consommation ne persiste, ne republie et ne déclenche rien. Aucune Inbox,
Outbox, migration, queue, Worker ou API HTTP n'est créée.
