# PublicProjection Routed Delivery Boundary V1

## Objet

La frontière V1 transporte une décision de routage déjà prise jusqu'au
Consumer. Elle ne route, ne persiste et ne consomme rien.

```text
Router owner
→ décision de destination
→ PublicProjectionRoutedDeliveryMessageV1
→ Routed Consumer
```

## Contrat versionné

```text
PublicProjectionRoutedDeliveryMessageV1

- deliveryMessage : PublicProjectionDeliveryMessage
- destination     : PublicProjectionDeliveryDestination
- routingProof    : PublicProjectionDeliveryRoutingProofV1
```

Le contrat est générique, immuable et indépendant d'IdentityAccess.

### Destination

`PublicProjectionDeliveryDestination` est une chaîne non vide et canonique.
Elle transporte une valeur déjà décidée. Elle ne possède aucune table de
routage et ne déduit jamais une destination depuis un type d'événement.

### Preuve de routage

```text
PublicProjectionDeliveryRoutingProofV1

- messageId
- sourceModule
- eventType
- destination
- routingVersion : 1
- checksum
```

Le checksum SHA-256 est calculé sur la concaténation canonique versionnée de
ces champs. La preuve lie la destination au message sans reconstruire la
décision.

## Invariants fermés

1. le `messageId`, le module source et le type d'événement de la preuve sont
   identiques à ceux du message ;
2. la destination de la preuve est identique à la destination transportée ;
3. la version vaut exactement `1` ;
4. le checksum correspond exactement aux champs canoniques ;
5. aucune propriété n'est nullable ;
6. toute divergence produit un rejet permanent avant consommation.

## Port Consumer additif

```text
PublicProjectionRoutedDeliveryConsumerV1::consumeRouted(
    PublicProjectionRoutedDeliveryMessageV1
): PublicProjectionDeliveryConsumptionResult
```

Le nom `consumeRouted` évite toute surcharge incompatible avec le port
historique :

```text
PublicProjectionDeliveryConsumer::consume(
    PublicProjectionDeliveryMessage
): PublicProjectionDeliveryConsumptionResult
```

Le port historique demeure strictement inchangé.
