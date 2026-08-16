# Portfolio Integration

`GET /api/authoring/portfolio` est retenu comme index autoritatif des parcours accessibles par l’AccountId de session.

Il expose actuellement :

- `listingId` ;
- `propertyId` ;
- `relation` ;
- `draftVersion` ;
- `ownershipVersion` ;
- `completenessCode` ;
- `sourceCheckpoint`.

Il n’expose ni état Aggregate, ni état Workflow, ni version Property, ni Media, ni Geography. Il ne peut donc pas être utilisé seul comme snapshot de reprise.

La future UI peut l’utiliser pour présenter les candidats. Après sélection, le Resume Reader doit relire toutes les sources autoritatives et ne jamais faire confiance aux versions du Portfolio sans comparaison. Aucun champ Portfolio supplémentaire n’est décidé par ce Blueprint.
