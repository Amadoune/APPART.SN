# Lead Eligibility Materialization Port Blueprint

## Propriétaire

Le futur port applicatif appartient à ContactsLeads. Il matérialise un lot déjà décidé ; il ne constitue ni un Repository d'Aggregate, ni un adaptateur `ListingCatalog` ou `AdvertiserCatalog`.

## Entrée conceptuelle fermée

- `ListingId` ;
- relation normative optionnelle vers un `AdvertiserId` ;
- décision `ListingContactability` ;
- Advertiser évalué ;
- décision `AdvertiserEligibility` ;
- unique `EligibilityRevision` partagée.

## Garanties

Le port futur devra refuser : relation multiple, révisions divergentes, version non croissante, décision implicite et rejeu divergent. Un rejeu byte-for-byte identique sera idempotent.

Il ne devra jamais : lire un Aggregate, interpréter un historique, recalculer une décision, créer un instant ou une identité, ni construire `LeadEligibilityProof`.

## Hors sprint

Aucune interface PHP, implémentation, table, migration, writer ou binding n'est créé en 4.4C-R2. Leur forme technique sera définie par 4.4C-S2 à partir de ce blueprint certifié.
