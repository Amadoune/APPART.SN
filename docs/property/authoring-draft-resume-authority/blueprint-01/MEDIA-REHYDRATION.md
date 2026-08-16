# Media Rehydration

`GET /api/authoring/properties/{propertyId}/media` restitue, sous contrôle owner :

- collectionId et version ;
- mediaId ;
- type ;
- checksum ;
- ordre ;
- caption ;
- statut ;
- indicateur primary.

Cela suffit pour restaurer identité, disponibilité, ordre et compteur. Cela ne suffit pas à restaurer l’image de Preview : aucune URL binaire owner-scoped n’est exposée, et les object URLs du navigateur disparaissent au reload.

La future Implementation doit soit afficher une reprise metadata-only explicitement qualifiée, soit ajouter une lecture binaire owner-scoped bornée à Media Authoring. La seconde option est nécessaire si la Preview doit conserver l’image réelle. Elle reste additive, sans migration et sans modification F6/F7, mais exige validation sécurité (ownership, type, cache privé, aucune URL publique prématurée).
