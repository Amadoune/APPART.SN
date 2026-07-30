# Phase 4.7F — Administrative Action Lifecycle Event Transport Analysis

## Décision

Le transport conserve l'événement certifié 4.7E comme une chaîne JSON canonique opaque. Il ne lit, ne complète et ne transforme aucun champ métier.

## Chaîne contractuelle

```text
AdministrativeActionLifecycleEvent
→ AdministrativeActionLifecycleDeliveryPayload
→ AdministrativeActionLifecycleTransportEnvelope V1
→ AdministrativeActionLifecycleEventRouter
```

Le payload Delivery contient exclusivement `canonicalEvent`. Son checksum SHA-256 porte sur les octets exacts de cette chaîne.

## Identités

* `eventId` est l'identité métier 4.7E et demeure inchangée ;
* `messageId` est l'identité technique du transport ;
* `messageId` est dérivé de manière déterministe des octets de `canonicalEvent` ;
* les deux identités ne sont jamais substituées ni régénérées.

## Frontière

Aucun routeur concret, stockage, Runtime, Worker, Consumer ou mécanisme de publication n'appartient à 4.7F.
