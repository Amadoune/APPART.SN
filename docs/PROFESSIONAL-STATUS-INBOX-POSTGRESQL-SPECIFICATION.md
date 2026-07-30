# Professional Status Inbox PostgreSQL Specification

La table append-only `professionals.professional_status_event_inbox` conserve l'événement canonique et l'enveloppe de transport byte-for-byte. `message_id` est unique, le statut initial est exclusivement `pending` et l'index `(status, message_id)` fournit un ordre de reprise déterministe.

La migration additive 029 et son rollback sont autonomes. Un verrou advisory transactionnel par `messageId` sérialise les écritures concurrentes.
