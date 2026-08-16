# Preuve de retry

Après le closed failure, la même commande Submit est rejouée avec les composants productifs.

La Property et l’unique ligne ledger Promotion déjà durables font converger la Promotion par replay compatible. La tentative Listing repart de Draft et réussit.

État terminal observé :

- Aggregate Listing Submitted ;
- Workflow Submitted avec une seule transition Submit ;
- un seul public-facts handoff ;
- un seul message Outbox ;
- une seule delivery ;
- un seul item PublicationReview ;
- une seule Property ;
- une seule ligne ledger Promotion.

Aucune duplication des handoffs n’est observée.
