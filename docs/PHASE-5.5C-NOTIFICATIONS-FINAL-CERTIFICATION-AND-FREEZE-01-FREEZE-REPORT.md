# Notifications — Freeze Report

Le gel couvre toutes les Foundations 5.5C Notifications et leurs surfaces
certifiées : contrats V1, Persistence, Runtime, Owner Readers, HTTP, Event,
Delivery et Outbox.

Les migrations `079_notifications_owner_source.sql` et
`080_notifications_outbox.sql`, ainsi que leurs rollbacks, sont gelées. Aucune
migration n'est ouverte.

Toute évolution future de cette capacité exige un amendement versionné et une
nouvelle décision d'autorité. Transport, Routing et Consumer demeurent hors du
périmètre certifié et non ouverts.
