# F4-A — Property Authoring Geography Selection Experience Authority 01

## Décision

Le workspace Property Authoring expose une unique surface de lecture authentifiée composant directement `GeographySelectionReaderV1`. Geography Application reste l’unique source des identités et de leur selectability.

Chaîne retenue :

`Session IAM → GET Authoring Geography → GeographySelectionReaderV1 → items minimaux → choix explicite → preuve de sélection rejouée au save`

Le navigateur transporte une identité sélectionnée ; il ne la crée, ne la certifie et ne la reconstruit jamais depuis un label.

## Route retenue

`GET /api/authoring/geography/selections`

Cette route appartient à l’expérience owner-scoped existante, sans transférer l’ownership des Places à Property Authoring. Une API Geography publique parallèle n’est pas créée.

## Décisions fermées

- query exacte F1 : `type`, `parentPlaceId`, `cursor`, `limit` ;
- résultat HTTP minimal ;
- navigation selon les enfants réellement retournés ;
- identité finale explicitement choisie ;
- validation serveur obligatoire par rejeu de la page F1 ;
- revalidation Domain lors de Promotion ;
- `city` et `neighborhood` dépréciés comme autorités ;
- aucune persistance ou migration dans F4-A.

## Verdict

La frontière est complètement qualifiée et ne laisse aucun choix requis implicite. **GO PROPOSÉ.**
