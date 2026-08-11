# APPART.TEST LISTING PUBLICATION COMMAND CONTRACT 01 — Command Contract Specification

## Statut

`NOT_MATERIALIZED`

Un command contract complet ne peut pas être certifié tant que ses champs ne possèdent pas tous une source d'autorité.

## Forme minimale candidate, non ouverte

Les transitions ne partagent pas les mêmes préconditions :

- Submit exige identité Listing, versions workflow/Aggregate, revision et evidence advertiser ;
- BeginReview exige revision et evidence de modération ;
- ApproveAndPublish exige en plus collection média et expiration autoritatives.

Il serait donc préférable de qualifier trois commandes distinctes plutôt qu'un DTO générique rempli de valeurs optionnelles. Cette orientation est documentaire et ne crée aucun contrat.

## Amendements requis

1. autorité de réservation des révisions Listing pour chaque transition ;
2. catalogue owner-scoped des evidence de transition ou commandes les portant explicitement ;
3. politique autoritative d'expiration de publication ;
4. décision explicite sur la résolution de la collection média.
