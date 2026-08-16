# Preuves d’intégration Submit

## Succès certifié F6

Le test PostgreSQL compose les stores productifs : Listing Draft → Promotion → Property réelle → transition Listing `Submitted`. La Property existe avant l’appel au workflow Submit.

## Refus Promotion certifié F6

Une Geography devenue disabled retourne un rejet Promotion. Le Listing reste `Draft`, aucune Property n’est créée et aucune transition `Submitted` n’est écrite.

## Échec après Promotion

L’inspection de la composition révèle une absence de rollback sur résultat fermé non réussi du workflow. `SubmitListing` peut avoir persisté l’Aggregate avant ce résultat ; la closure transactionnelle retourne normalement et est commitée. La reprise `AlreadyApplied → retry Listing` ne peut donc pas être certifiée sans corriger cette frontière.
