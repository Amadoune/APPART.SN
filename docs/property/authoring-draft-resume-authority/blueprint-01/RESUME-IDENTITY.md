# Resume Identity

L’identité minimale transportée est `listingId`.

Le `propertyId` est résolu depuis le Listing Draft et l’ownership persistants, puis comparé au Portfolio, à l’Aggregate et à la Property Authoring. Le transporter en parallèle créerait deux sources client susceptibles de diverger.

`revisionId` n’est pas une identité de reprise : il sert à la création initiale et ne doit ni être régénéré ni être requis pour Submit. Les versions restaurées sont :

- `expectedVersion` ← version du Listing Draft ;
- `expectedAuthoringVersion` ← version de Property Authoring.

Aucun nouveau `PropertyId`, `ListingId`, `AddressIntentId`, commandId ou Promotion identity n’est émis pendant la lecture.
