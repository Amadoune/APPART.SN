# Reservation Lifecycle Event Serialization Specification

La forme JSON canonique est un objet plat de onze champs ordonnés :

```text
eventId
aggregateType
eventType
payloadVersion
reservationId
transition
previousState
currentState
action
version
occurredVersion
```

Les valeurs enum sont sérialisées avec leur valeur canonique, les versions comme entiers et l'identité Reservation comme UUID minuscule. Aucun champ optionnel, `null`, timestamp, espace décoratif ou tri dynamique n'est admis.

L'encodage utilise JSON UTF-8 sans échappement des barres obliques ni des caractères Unicode. L'ordre est construit explicitement par `ReservationLifecycleEventSerializer`; il ne dépend ni de la réflexion ni de l'ordre interne d'un objet. Un même événement produit ainsi les mêmes octets.

Cette forme est un contrat métier sérialisable. Elle ne constitue pas encore un payload Delivery, un message Outbox ou une enveloppe de transport.
