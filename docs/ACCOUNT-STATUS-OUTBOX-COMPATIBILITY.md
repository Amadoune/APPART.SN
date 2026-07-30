# Account Status — Outbox Compatibility

## Chaîne certifiable

```text
PublicProjectionDeliveryMessage
+ destination décidée par Router 4.9H
→ PublicProjectionRoutedDeliveryMessageV1
→ Writer générique
→ identity_access.public_projection_outbox_*
→ Reader générique
→ Worker générique (mode routed-v1)
→ AccountStatusDeliveryConsumer::consumeRouted
→ vérification routing proof
→ consume(message, destination) 4.9I
```

## Owner

```text
module          : IdentityAccess
schema          : identity_access
aggregate       : AccountStatus
events          : account.status.suspended
                  account.status.reactivated
migration       : 043
delivery mode   : routed-v1
```

## Identités distinctes

- `eventId` identifie le fait métier Account Status ;
- `messageId` du transport Account Status identifie l'enveloppe 4.9G ;
- `idempotencyKey` générique identifie la position causale Outbox ;
- `messageId` générique dérive exclusivement de cette idempotency key ;
- le checksum de routing proof lie le message générique à la destination.

Aucune de ces identités n'est substituée à une autre.

## Extensions génériques

- le catalogue accepte les deux événements `account.status.*` ;
- le Mapper restaure `AccountStatusDeliveryPayload` ;
- le Writer et le Reader conservent destination, version et checksum ;
- le registre sélectionne explicitement `routed-v1` ;
- le Worker branche uniquement sur le mode générique déclaré ;
- le Consumer existant traduit la matrice fermée certifiée par R3.

## Absence de spécialisation

Aucun Writer, Reader, Mapper, Worker ou adapter IdentityAccess/Account Status
n'est créé. Les dix owners historiques restent en mode `legacy` et leurs ports
demeurent inchangés.
