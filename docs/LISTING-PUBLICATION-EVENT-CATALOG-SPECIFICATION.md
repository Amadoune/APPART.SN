# Listing Publication Event Catalog Specification

## Périmètre

Le catalogue contractuel couvre exactement les vingt-sept transitions autorisées par le workflow 4.1A. Chaque transition produit un et un seul fait métier. Une transition non certifiée est refusée explicitement.

## Types fermés

La version initiale définit quinze types : soumission, resoumission, début de revue, revue après modification matérielle, revue de renouvellement, revue de republication, publication, demande de changements, rejet, retrait, suspension, rétablissement, expiration, renouvellement et archivage.

`ListingPublicationInitialized` n'appartient pas à ce catalogue : l'initialisation du store n'est pas une transition du workflow 4.1A. Son introduction nécessiterait un contrat métier dédié.

## Cardinalité et ordre

Chaque transition produit une liste contenant exactement un événement. La position unique constitue l'ordre déterministe initial. Une future version pourra étendre cette cardinalité uniquement par une nouvelle décision contractuelle certifiée.

Le catalogue ne consulte pas le workflow et ne décide rien : sa matrice explicite est la référence unique transition → événement.
