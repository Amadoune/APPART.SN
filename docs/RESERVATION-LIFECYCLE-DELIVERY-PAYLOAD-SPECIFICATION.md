# Reservation Lifecycle Delivery Payload Specification

## Forme

Le payload Delivery est un objet immuable contenant exactement :

```json
{"canonicalEvent":"<événement JSON canonique 4.3E>"}
```

La valeur de `canonicalEvent` est produite exclusivement par `ReservationLifecycleEventSerializer`. Le transport ne renomme, ne trie, ne normalise et ne convertit aucun de ses onze champs métier.

## Intégrité

`checksum()` retourne le SHA-256 hexadécimal des octets exacts de `canonicalEvent`. Lors d'une restauration, sont refusés :

- une enveloppe absente, enrichie ou mal typée ;
- un JSON ou une forme métier invalide ;
- un enum, UUID ou entier invalide ;
- une identité métier incohérente ;
- un ordre de champs non canonique ;
- toute représentation qui diffère après sérialisation canonique.

La restauration restitue le même événement et les mêmes octets. Elle ne crée aucune nouvelle identité métier et ne réalise aucune publication.
