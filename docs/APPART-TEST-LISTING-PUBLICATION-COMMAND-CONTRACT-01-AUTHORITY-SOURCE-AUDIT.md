# APPART.TEST LISTING PUBLICATION COMMAND CONTRACT 01 — Authority Source Audit

## Verdict

`NO GO PROPOSÉ`

Toutes les données indispensables ne possèdent pas une source autoritative existante disponible au moment des transitions.

## Sources qualifiées

- l'acteur Submit peut provenir de la session IAM et de l'ownership authoring ;
- l'acteur de modération et l'instant peuvent provenir de la décision de modération certifiée ;
- le rattachement média peut être lu par `MediaCollectionOwnershipLookup`, sous réserve d'un résultat unique ;
- l'instant HTTP du workflow est porté par `ListingPublicationEventMetadata`.

## Absences bloquantes

- aucun contrat ne réserve ou fournit un `ListingRevisionId` pour Submit, BeginReview ou ApproveAndPublish ;
- aucune politique Application ne fournit l'`ExpirationDate` de publication ;
- les commandes workflow ne portent ni reason ni origin Aggregate ;
- le trigger Aggregate n'est pas exposé par une autorité de commande, même si une correspondance technique avec l'action pourrait être imaginée.

Toute déduction dans le composite constituerait une nouvelle décision métier interdite.
