# Listing Publication Delivery Guarantee Specification

## Garantie

La frontière de livraison est l'Inbox `listing_lifecycle.publication_event_inbox`. Une réponse `Routed` garantit qu'une ligne durable contient le `canonicalEvent` exact et son checksum.

## Idempotence

`event_id` est unique. Une nouvelle livraison identique produit `AlreadyStored`. Une même identité accompagnée d'un type, d'une version, d'un contenu canonique ou d'un checksum divergent produit `Rejected`.

## Identités

`inbox_id` est une identité technique dérivée par SHA-256 de l'`eventId`. L'identité métier est conservée séparément et ne peut être remplacée.

## Absence d'acquittement prématuré

Une indisponibilité, une erreur temporaire, une contrainte invalide ou une divergence ne produit jamais `Routed`. Le bootstrap et Runtime Health ne transfèrent aucun événement.
