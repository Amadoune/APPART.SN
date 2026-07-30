# Listing Publication Event Versioning Specification

## Version initiale

`ListingPublicationEventPayloadVersion::V1` est l'unique version certifiée. Le numéro de version appartient à l'enveloppe événementielle et précède le payload dans la sérialisation canonique.

## Compatibilité

Une modification de nom, type, présence, ordre ou sémantique d'un champ exige une nouvelle version. V1 reste immuable. Une nouvelle version ne peut ni réinterpréter un événement V1, ni modifier son identité.

## Sérialisation canonique

L'ordre racine est : `eventId`, `eventType`, `payloadVersion`, `payload`, `metadata`. Le payload et les métadonnées suivent l'ordre défini dans leurs spécifications. JSON utilise UTF-8, sans échappement Unicode ni slash, et échoue sur toute valeur non sérialisable.

Cette stratégie est applicative et ne dépend d'aucun mapper ou schéma Outbox.
