# Final Published Handoff

Le chemin normal consomme le transport certifié de `ListingPublished` (`listing.publication.published`, payload V1). Son eventId est déjà déterministe à partir de ListingId, publicationVersion, type et payload version.

L'événement est un trigger de relecture, pas une source de Property, Media, rang ou facettes. Un consumer SearchDiscovery borné appelle le même matérialiseur que le catch-up avec le ListingId.

La transaction Listing/outbox reste close avant la consommation. La matérialisation ouvre uniquement une transaction SearchDiscovery : aucune transaction distribuée.
