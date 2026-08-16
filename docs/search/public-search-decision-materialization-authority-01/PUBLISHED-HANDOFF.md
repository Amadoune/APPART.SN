# Handoff Published

Le catalogue ListingLifecycle certifie `under_review → approve_and_publish → published` comme `ListingPublicationEventType::ListingPublished`, valeur transportée `listing.publication.published`.

Le payload V1 contient :

- ListingId ;
- état précédent et état courant ;
- action ;
- `publicationVersion` ;
- métadonnées `occurredAt/recordedAt` ;
- identité déterministe dérivée de type, version payload, ListingId et publicationVersion.

L'événement est durablement routable via l'outbox Public Projection existante, mais aucun consumer SearchDiscovery ne le transforme en `SearchDecision`. Le payload est un trigger suffisant pour relire des sources owner-scoped; il est insuffisant pour choisir un rang ou des facettes sans autorités complémentaires.

Aucun nouveau handoff n'est décidé pendant ce NO GO.

## Completion 01

`ListingPublished` est désormais certifié comme trigger suffisant. Un consumer SearchDiscovery relit les owners et appelle le matérialiseur commun ; aucun nouveau fait métier ni transaction distribuée n'est introduit.
