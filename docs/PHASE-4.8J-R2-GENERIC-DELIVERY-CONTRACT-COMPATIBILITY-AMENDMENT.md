# Phase 4.8J-R2 — Generic Delivery Contract Compatibility Amendment

## Constat bloquant

Le futur 4.8J impose la réutilisation exclusive du catalogue, du mapper et du
Worker `PublicProjection*`. Ces composants acceptent exclusivement :

```text
PublicProjectionDeliveryPayload
PublicProjectionDeliveryConsumer
consume(PublicProjectionDeliveryMessage)
```

Les contrats certifiés Place Lifecycle exposent actuellement :

```text
PlaceLifecycleDeliveryPayload
→ aucun PublicProjectionDeliveryPayload

PlaceLifecycleDeliveryConsumer
→ aucun PublicProjectionDeliveryConsumer
→ consume(PlaceLifecycleTransportEnvelope)
```

## Impossibilité

Le catalogue générique exige une classe de payload implémentant son port.
Le mapper générique doit restaurer ce même port. Le registre du Worker exige
un Consumer générique.

4.8J ne peut donc pas satisfaire J3, J4 et J5 sans :

- modifier les contrats gelés 4.8G et 4.8I;
- ou créer un payload/Consumer adapter spécialisé;
- ou modifier les ports génériques historiques.

Ces trois options sont interdites par le mandat actuel.

## Question normative

```text
Quelle adaptation contractuelle minimale autorise la compatibilité générique
sans dupliquer Payload, Consumer, Writer, Reader, Mapper ou Worker ?
```

## Options auditées

1. amendement versionné de 4.8G et 4.8I afin que les classes existantes
   implémentent les ports génériques, avec signature Consumer compatible;
2. port d'adaptation générique non spécialisé, si sa compatibilité avec les
   neuf owners est démontrée;
3. abandon de la réutilisation générique — option présumée NO GO car contraire
   à R1.

## Décision normative

**Option 1 retenue** : amendement versionné, additif et minimal des classes
existantes 4.8G et 4.8I.

```text
PlaceLifecycleDeliveryPayload
→ implémente PublicProjectionDeliveryPayload
→ aucune propriété, sérialisation ou sémantique modifiée

PlaceLifecycleDeliveryConsumer
→ implémente PublicProjectionDeliveryConsumer
→ consume(PublicProjectionDeliveryMessage)
→ valide l'enveloppe Delivery générique
→ restaure le PlaceLifecycleDeliveryPayload existant
→ reconstruit PlaceLifecycleTransportEnvelope
→ délègue au même Router 4.8H
→ applique la même Policy 4.8I
```

La signature historique de consommation d'enveloppe devient une opération
interne nommée conceptuellement `consumeEnvelope`. La matrice
Ack/Retry/Quarantine demeure strictement inchangée.

## Versionnement

L'amendement autorise exclusivement, pendant 4.8J :

- l'ajout de l'interface marker au payload existant;
- l'ajout de l'interface Consumer générique à la classe existante;
- l'adaptation de sa frontière d'entrée au message générique;
- les validations structurelles nécessaires avant reconstruction de
  l'enveloppe 4.8G.

Il n'autorise aucun nouveau Payload, Consumer ou adapter.

## Compatibilité historique

Les ports `PublicProjectionDeliveryPayload` et
`PublicProjectionDeliveryConsumer` restent inchangés. Les neuf owners
historiques continuent donc à compiler, être restaurés et être enregistrés
exactement comme avant.

Writer, Reader, Mapper, Worker, catalogue générique et conventions d'identité
ne changent pas de signature.

## Rejet des autres options

- **Option 2 — NO GO** : elle introduirait une deuxième voie d'adaptation et
  déplacerait une spécificité Place dans une abstraction prétendument
  générique.
- **Option 3 — NO GO** : elle contredit directement la résolution R1 et
  dupliquerait la chaîne Outbox.

## Ordre normatif de reprise de 4.8J

```text
Amendement minimal des classes existantes
→ tests de conservation sémantique 4.8G / 4.8I
→ extension catalogue et mapper génériques
→ mapping Geography ↔ geography
→ migration 040
→ registrations du Worker générique
→ validations J1 à J6
```

## Contraintes maintenues

Aucune migration 040, table, mapping owner, catalogue, mapper, registration
Worker, binding Runtime ou implémentation Outbox ne peut être créée avant la
certification de cet amendement.
