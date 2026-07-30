# Property Lifecycle Event Inbox Migration & Rollback Guide

La migration `018_property_lifecycle_event_inbox.sql` crée uniquement `real_estate_catalog.property_lifecycle_event_inbox`, ses contraintes et l'index de reprise.

Le rollback `018_property_lifecycle_event_inbox.down.sql` supprime uniquement cette Inbox. Il ne touche ni au journal Property Lifecycle 017, ni au Domain Property, ni à l'Inbox Listing Publication.

La campagne PostgreSQL applique le rollback, vérifie l'absence de la table, rejoue la migration et confirme sa recréation exacte.
