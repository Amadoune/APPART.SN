# Checksum and idempotency

V2 possède un payload différent de V1 et donc un checksum différent. La source revision Geography V2 et le schemaVersion participent à la cohérence.

Replay V2 identique → même checksum/read model. V1 et V2 ne sont jamais déclarés payload équivalent.
