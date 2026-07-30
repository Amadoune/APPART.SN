# Listing Publication Production Routing Specification

## Séquence

1. Le routeur reçoit un `ListingPublicationEvent` restauré.
2. La destination produit sa sérialisation canonique exacte.
3. Elle dérive `inbox_id` depuis `eventId` et calcule SHA-256.
4. Elle insère dans l'Inbox avec le statut `pending`.
5. En conflit d'identité, elle compare toutes les propriétés persistées.
6. `Stored` ou `AlreadyStored` autorisent `Routed`; aucune autre réponse ne l'autorise.

Le routeur possède une seule implémentation de production et la destination une seule implémentation PostgreSQL.
