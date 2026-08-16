# Modèle d’ownership des ressources

## Distinction

Un rôle IAM autorise une responsabilité globale spécialisée. L’ownership Authoring est une relation entre un AccountId et une ressource précise.

## Dérivation

- Property Authoring : l’AccountId de session est persisté comme `ownerAccountId` à la création.
- Listing Draft : le créateur authentifié devient owner dans `ListingOwnershipState`.
- Media : le Controller transmet l’AccountId de session ; le Runtime exige qu’il corresponde au Property owner.
- Preview/read/update : owner ou délégation explicite selon `VIEW`/`EDIT`.
- Submit : owner ou délégation explicite `SUBMIT`, puis promotion avec le même actor.

Le client ne choisit jamais l’owner. Toute discordance est réduite en résultat fermé NotFound/Forbidden ou équivalent.
