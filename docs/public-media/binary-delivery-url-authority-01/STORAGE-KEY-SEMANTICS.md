# Storage key semantics

`storageKey` est une clé opaque interne construite sous `owners/{ownerId}/assets/{assetId}`. Elle adresse le backend privé et n'est ni une URL, ni un chemin public, ni une identité exposable.

Elle reste stable pour un objet existant, mais dépend du backend et de l'owner. Elle ne doit apparaître dans aucun locator, response, log public ou décision Public Media.
