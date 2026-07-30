# Account Status — Event Routing

Le routeur consomme exclusivement `AccountStatusDeliveryMessage` et produit une
décision en mémoire.

```text
account.status.suspended ─┐
                          ├→ identity_access.account_status.lifecycle_facts
account.status.reactivated┘
```

Le résultat conserve la même instance du message, donc `messageId`, `eventId`,
payload canonique et checksum restent strictement inchangés.

Résultats fermés :

```text
Routed(destination)
Rejected(UnsupportedMessage | CorruptedMessage)
```

Le routeur valide le round-trip canonique avant la sélection. Il ne livre,
persiste ou publie rien et n'introduit aucun Consumer, Inbox, Outbox, Worker ou
HTTP. La destination est logique; son implémentation appartient aux jalons
ultérieurs.
