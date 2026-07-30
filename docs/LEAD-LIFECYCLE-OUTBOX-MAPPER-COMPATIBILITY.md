# Lead Lifecycle Outbox Mapper Compatibility

Le mapper générique sérialise `fields()` avec l'ordre canonique et conserve le checksum du payload Delivery.

À la lecture, un type Lead appelle exactement `LeadLifecycleDeliveryPayload::restore($data)`. Cette restauration vérifie forme, ordre, types, identité événementielle, checksum et reproduction byte-for-byte de `canonicalEvent`.

Le mapper conserve séparément `eventId` métier dans l'événement et `message_id` technique dans l'Outbox. Il ne recalcule ni ne substitue l'un à l'autre.
