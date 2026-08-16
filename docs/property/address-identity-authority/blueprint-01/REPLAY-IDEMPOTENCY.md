# Replay and Idempotency

- Même propertyId + même addressIntentId : même UUIDv5.
- Même commandId rejoué : aucune nouvelle identité.
- Nouveau commandId pour la même intention après timeout ou échec Listing : même identité.
- Nouvelle intention Address : addressIntentId distinct, donc AddressId distinct.
- Aucun instant courant, UUID client ou état Projection n'intervient.

Une réponse perdue ne requiert aucune récupération de ledger : le caller recalcule la valeur. Si le Property est déjà promu avec cette identité et ces faits, la promotion classe le replay comme compatible/AlreadyApplied ; l'Issuer reste une fonction pure.
