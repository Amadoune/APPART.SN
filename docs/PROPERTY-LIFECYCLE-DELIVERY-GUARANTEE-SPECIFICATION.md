# Property Lifecycle Delivery Guarantee Specification

Le transfert offre une idempotence stricte par `event_id` :

- première écriture complète : `Stored` ;
- nouvelle écriture byte-for-byte identique : `AlreadyStored` ;
- même identité avec contenu différent : `Rejected`.

`inbox_id` est l'identité technique `plei:` suivie du SHA-256 de `eventId`. Les deux identités sont conservées simultanément et ne sont jamais interchangeables.

L'index partiel `(status, inbox_id) WHERE status = 'pending'` fixe un ordre de reprise déterministe. Il n'implique aucun Worker ni politique de traitement.
