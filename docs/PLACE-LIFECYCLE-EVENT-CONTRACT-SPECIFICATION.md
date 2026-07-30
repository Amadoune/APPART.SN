# Place Lifecycle Event Contract — Spécification

## Catalogue V1 fermé

Le catalogue contient exclusivement :

```text
place.lifecycle.enabled
place.lifecycle.disabled
place.lifecycle.merged
```

Chaque événement porte explicitement `payloadVersion = 1`.

## Payload minimal

Tous les faits portent :

- `placeId`;
- `previousState`;
- `action`;
- `currentState`;
- `occurredVersion`.

Seul `place.lifecycle.merged` porte également `targetPlaceId`. Les faits
Enabled et Disabled ne transportent aucune preuve de cible.

L'instant métier UTC appartient au contrat d'événement. L'acteur, l'intention
d'idempotence et les observations ayant permis la décision n'appartiennent pas
au fait publié.

## Identité déterministe

```text
eventId = SHA-256(
    eventType
    + payloadVersion
    + placeId
    + previousState
    + action
    + currentState
    + occurredVersion
    + targetPlaceId éventuel
)
```

La même transition durable produit la même identité. Une action, une version
ou une cible différente produit une identité différente.

## Frontière

Le contrat ne sérialise, ne publie et ne route aucun événement. Il ne dépend
d'aucun Runtime, transport, Outbox, HTTP, Consumer ou Worker.
