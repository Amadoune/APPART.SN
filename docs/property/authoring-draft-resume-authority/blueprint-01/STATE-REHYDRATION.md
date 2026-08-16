# State Rehydration

| Champ client | Source autoritative |
|---|---|
| propertyId | Listing Draft, vérifié contre ownership/Portfolio |
| listingId | sélection URL, vérifiée server-side |
| revisionId | non restauré, non requis après création |
| expectedVersion | Listing Draft.version |
| expectedAuthoringVersion | PropertyAuthoring.version |
| transaction/project | ListingDraft.transactionKind |
| PropertyType, référence, surface, rooms, bathrooms, année, adresse | PropertyAuthoringState |
| title, description, prix, charges, disponibilité, contact | ListingDraftState |
| Geography | GeographicPlaceId puis résolution Registry |
| Media | Media Collection owner-scoped |
| step | dérivation de complétude |

Le bootstrap doit être sérialisé server-side ou obtenu par un endpoint Resume unique. `authoring.js` reçoit ce snapshot ; il ne fabrique aucun identifiant lorsque le mode est `resume`.

Les labels legacy `city`/`neighborhood` sont affichables mais ne remplacent jamais `GeographicPlaceId`.
