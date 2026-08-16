# Owner Authority

L’autorité d’accès est :

`AuthenticatedSessionContext → AccountId → ownership server-side`.

Le client ne transmet jamais `ownerAccountId`. Un `listingId` valide ne constitue pas une autorisation. Le Reader doit relire `ListingOwnershipState`, puis vérifier :

- owner : égalité constante avec l’AccountId de session ;
- délégation : permission `VIEW` pour lecture et `EDIT` pour reprise modifiable ;
- Property : `ownerAccountId` cohérent avec l’owner autoritatif du Listing.

Pour éviter l’énumération, absence et défaut d’autorisation sont exposés en HTTP sous un même `404`. Le résultat interne peut conserver une cause d’audit, mais elle ne doit pas être révélée au client.

Une session absente ou expirée produit `401`. Aucun UUID, paramètre URL, localStorage ou donnée du formulaire ne peut remplacer la session IAM.
