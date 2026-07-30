# Lead Lifecycle Event Contract Specification

Le catalogue fermé contient `lead.lifecycle.delivered`, `lead.lifecycle.rejected` et `lead.lifecycle.closed`. Les deux transitions vers `Closed` partagent le même fait, tandis que le payload conserve leur état précédent exact.

L'enveloppe canonique est `eventId → eventType → payloadVersion → payload → metadata`. L'identité SHA-256 couvre type, version, LeadId, transition exacte et version survenue. Les métadonnées imposent acteur, `occurredAt` et `recordedAt` UTC explicites.

Le payload V1 interdit ListingId, AdvertiserId, visiteur, canal, coordonnées, sujet, message, consentement et preuve d'éligibilité. Il ne contient que l'identité Lead et le fait de cycle de vie minimal.

Aucun transport, routeur, Inbox, Outbox, Consumer, Worker, HTTP ou publication n'appartient à cette fondation.
