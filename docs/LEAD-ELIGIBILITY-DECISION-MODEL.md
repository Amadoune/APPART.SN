# Lead Eligibility Decision Model

## Modèle certifié

Le modèle réutilise exclusivement les ensembles fermés historiques :

- `ListingContactability` : `Contactable`, `Missing`, `NotPublished`, `Closed` ;
- `AdvertiserEligibility` : `EligibleRecipient`, `Missing`, `Suspended`, `NotListingRecipient` ;
- `EligibilityRevision` : identifiant de cohérence, version et instant effectif explicites.

Aucun enum concurrent n'est introduit.

Une décision d'éligibilité Lead est un lot cohérent composé de trois faits déjà décidés par leurs propriétaires : une contactabilité Listing, une relation normative Listing–Advertiser et une éligibilité Advertiser. ContactsLeads assemble leur matérialisation sous une même révision, sans les recalculer.

## Frontières

ListingLifecycle décide la contactabilité et possède la relation normative. ContactsLeads décide uniquement si l'Advertiser peut recevoir un Lead. Le producteur futur transmet des faits certifiés et une révision explicite au port de matérialisation décrit par le blueprint ; l'infrastructure ne classe aucun état.
