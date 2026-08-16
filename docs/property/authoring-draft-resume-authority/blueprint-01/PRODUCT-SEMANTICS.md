# Product Semantics

Un parcours reprenable est un ensemble cohérent :

- `PropertyAuthoringState` ;
- `ListingDraftState` et ownership ;
- Listing Aggregate et Publication Workflow à l’état `draft` ;
- collection Media liée à la Property ;
- `GeographicPlaceId` et `AddressIntentId` persistés.

Les stores propriétaires de ces faits restent autoritatifs. Le Portfolio n’est qu’un index de découverte, et le snapshot de reprise n’est qu’une projection éphémère read-only.

Un parcours Submitted, UnderReview ou Published n’est pas un draft reprenable. Il doit être présenté dans sa surface produit propre, sans réouverture silencieuse de l’Authoring.

La reprise restaure les données et versions nécessaires pour continuer. Elle ne promet pas de restaurer un état DOM, un `FileList`, un object URL, un curseur de pagination Geography ou l’ancien step du navigateur.
