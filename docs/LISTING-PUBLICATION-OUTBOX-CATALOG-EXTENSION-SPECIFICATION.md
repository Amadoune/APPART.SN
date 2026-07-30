# Listing Publication Outbox Catalog Extension Specification

Le catalogue officiel contient :

- les cinq entrées historiques inchangées ;
- une entrée V1 pour chaque cas de `ListingPublicationEventType` ;
- source `ListingLifecycle` ;
- aggregate `Listing` ;
- payload technique `ListingPublicationDeliveryPayload`.

La reconnaissance vérifie type, version, source, aggregate, classe de payload et identité Listing. Un type inconnu ou une version inconnue conserve les résultats historiques `UnsupportedType` et `UnsupportedVersion`.

Le catalogue ne construit aucun événement et ne consulte aucune transition.
