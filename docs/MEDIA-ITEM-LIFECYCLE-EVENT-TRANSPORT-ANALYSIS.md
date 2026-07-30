# Media Item Lifecycle Event Transport Analysis

Le Sprint **4.6F** encapsule le contrat 4.6E sans modifier son contenu.

`MediaItemLifecycleDeliveryPayload` expose un seul champ : `canonicalEvent`. Le checksum SHA-256 est calculé directement sur ces octets.

L'enveloppe technique V1 sépare :

- `eventId`, identité métier déjà présente dans l'événement ;
- `messageId`, identité technique préfixée `media-item-lifecycle-delivery-`.

Le transport valide la forme canonique pour permettre une restauration exacte, mais ne prend aucune décision métier et n'enrichit jamais le payload événementiel.
