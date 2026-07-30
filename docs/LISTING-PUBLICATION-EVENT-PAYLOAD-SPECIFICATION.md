# Listing Publication Event Payload Specification

## Payload V1

Tous les événements de transition utilisent un payload immuable commun :

| Champ | Type | Règle |
|---|---|---|
| `listingId` | UUID Listing | identité métier existante |
| `previousState` | état fermé | état source de la transition |
| `state` | état fermé | état cible de la transition |
| `action` | action fermée | action certifiée appliquée |
| `publicationVersion` | entier | version persistée, au minimum 2 |

L'ordre des champs est normatif. Aucune donnée HTTP, PostgreSQL, Outbox ou Projection n'appartient au payload.

## Identité

`eventId` est dérivé par SHA-256 de `listingId`, `publicationVersion`, `eventType` et `payloadVersion`, avec le préfixe `listing-publication-`. Une même transition persistée produit donc la même identité sans UUID aléatoire.
