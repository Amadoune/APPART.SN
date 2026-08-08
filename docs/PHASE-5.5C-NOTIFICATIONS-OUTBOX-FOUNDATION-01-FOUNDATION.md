# Notifications Outbox Foundation

L'Outbox owner-scoped persiste exclusivement les `NotificationDeliveryV1` dans un journal append-only. L'identité et le checksum sont des SHA-256 déterministes issus d'une représentation JSON canonique. Le Repository PostgreSQL est l'unique enclave Infrastructure autorisée.

L'append est transactionnel, utilise un savepoint sous transaction externe et converge par clé primaire déterministe.
