# Final productive contract

## Decision V2

- schemaVersion : `public-geography-place-representation-v2`;
- identity : `terminalPlaceId`;
- status : `available` ou `unavailable`;
- locality : officialName terminal pour Available, `null` pour Unavailable;
- breadcrumb Available : root→leaf, items `{placeId,type,officialName,parentPlaceId,aggregateVersion}`;
- breadcrumb Unavailable : vide;
- revisionVector : root→leaf, items `{placeId,aggregateVersion}`, conservé dans les deux statuts;
- revision.version/watermark : somme contrôlée des aggregateVersion;
- checksum : SHA-256 du payload canonique ordonné;
- causation : identité explicite et déterministe du trigger.

Aucune URL, slug, code, alias, ListingId, PropertyId ou substitution de merge n'entre dans la décision. `label` est le nom consommateur de `officialName`; la couche source conserve `officialName`.

## Version

- absence : Applied si watermark positif;
- mêmes vecteur, payload et causalité : AlreadyApplied;
- vecteur avancé et watermark supérieur : Applied;
- watermark inférieur : RejectedObsolete;
- même watermark mais vecteur/payload/causalité divergents : Divergent.

Le watermark accélère le protocole monotone mais ne remplace jamais le vecteur.
