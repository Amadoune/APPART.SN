# Lead Lifecycle Inbox PostgreSQL Specification

La table propriétaire est `contacts_leads.lead_lifecycle_event_inbox`. Elle est append-only et contient : identité Inbox, `messageId`, type, version de transport, événement canonique, source, `eventId`, checksum, enveloppe canonique, statut et nombre de tentatives.

Les seules valeurs initiales autorisées sont `status = pending` et `delivery_attempts = 0`. Les trois types 4.4E et la version V1 sont contraints par PostgreSQL.

La contrainte unique sur `message_id` constitue l'autorité d'idempotence. L'index `(status, message_id)` fournit un ordre de reprise déterministe sans timestamp implicite.
