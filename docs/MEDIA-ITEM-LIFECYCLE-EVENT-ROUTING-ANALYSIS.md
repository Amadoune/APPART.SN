# Media Item Lifecycle Event Routing Analysis

Le Sprint 4.6G introduit une destination PostgreSQL durable pour les enveloppes certifiées en 4.6F. Le routeur valide uniquement leur cohérence technique, puis délègue au port `MediaItemLifecycleInboxStore`.

Il n'interprète ni l'événement, ni la transition, ni le contexte propriétaire de `MediaCollection`. L'événement canonique et l'enveloppe sont conservés comme des octets opaques.

L'Inbox est append-only et propriétaire du module `Media`. `messageId` est l'unique clé d'idempotence technique ; `eventId` reste une identité métier distincte.
