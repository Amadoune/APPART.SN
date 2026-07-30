# Lead Lifecycle Inbox Migration & Rollback Guide

Migration additive : `025_lead_lifecycle_event_inbox.sql`.

Rollback isolé : `025_lead_lifecycle_event_inbox.down.sql` supprime uniquement `contacts_leads.lead_lifecycle_event_inbox`.

Le schéma `contacts_leads`, le journal 022, les décisions 023 et le contexte 024 ne sont ni modifiés ni référencés par clé étrangère. Le rollback 025 est donc autonome.
