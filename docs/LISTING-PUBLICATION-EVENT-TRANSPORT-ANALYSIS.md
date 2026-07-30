# Listing Publication Event Transport Analysis

## Problème résolu

L'événement 4.1EA est un fait métier canonique. `PublicProjectionDeliveryMessage` est une enveloppe technique possédant sa propre identité et un payload soumis au contrat `PublicProjectionDeliveryPayload`. Les deux modèles ne doivent ni fusionner ni se connaître mutuellement.

## Adaptateur retenu

`ListingPublicationDeliveryPayload` est l'unique adaptateur. Il enveloppe un `ListingPublicationEvent` déjà construit et expose sa sérialisation canonique dans un champ technique unique `canonicalEvent`. Il ne calcule ni événement, ni transition, ni métadonnée.

Le checksum SHA-256 porte sur la chaîne canonique exacte. La restauration valide la forme, les types, l'identité dérivée et l'égalité byte-for-byte après resérialisation.

## Routage

`ListingPublicationEventRouter` reçoit exclusivement l'événement métier restauré. Cette fondation ne fournit aucune implémentation de production et n'acquitte aucun message. Le résultat fermé distingue routage réel, attente explicite, échec réessayable et rejet.
