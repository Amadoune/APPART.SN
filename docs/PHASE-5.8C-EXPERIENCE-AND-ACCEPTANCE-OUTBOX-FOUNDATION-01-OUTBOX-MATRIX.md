# Outbox Matrix

| Garantie | Matérialisation |
|---|---|
| Identité | SHA-256 déterministe owner/type/observedAt |
| Checksum | SHA-256 canonique type/status/observedAt |
| Idempotence | Applied, AlreadyApplied, DivergentMessage |
| Claim | FOR UPDATE OF state SKIP LOCKED |
| Retry | maximum dix tentatives |
| Lecture | ordre availableAt, createdAt, messageId |
| Transaction | savepoint local, rollback externe préservé |

EventType, status et observedAt sont conservés sans transformation.

