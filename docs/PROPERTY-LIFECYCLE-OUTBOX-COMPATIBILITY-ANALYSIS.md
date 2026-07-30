# Property Lifecycle Outbox Compatibility Analysis

## Décision

Le Sprint 4.2H étend l'infrastructure `Public Projection Delivery` existante. Il ne crée ni Outbox, ni mapper, ni registre parallèle. Les sept types certifiés par 4.2E utilisent le payload opaque 4.2F et le routeur durable 4.2G.

## Frontières

- Le catalogue contrôle type, version, module, agrégat, classe de payload et identité.
- Le mapper conserve l'enveloppe `canonicalEvent` et son checksum sans reconstruire de fait métier.
- Le Consumer restaure strictement le payload, vérifie la cohérence du message, puis appelle une fois le routeur.
- Le Worker sélectionne le Consumer par un couple type/version unique.
- L'orchestrateur 4.2D reste sans événement et sans Outbox.

La séparation des identités, le rejet des divergences et l'absence d'acquittement hors `Routed` empêchent toute corruption ou perte silencieuse.
