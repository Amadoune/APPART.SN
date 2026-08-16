# Public Search Ranking and Facet Source Authority 01

## Décision

SearchDiscovery est l'owner légitime du ranking et de la composition des facettes. Les faits sources restent possédés par ListingLifecycle, RealEstateCatalog, Media et Geography.

L'autorité ne peut pas être fermée : `SearchRank` ne définit qu'un entier valide de 0 à 10000. Aucun code ou document certifié ne définit le sens de l'ordre, une valeur neutre, une formule ou les entrées d'un ranking public. Les documents historiques qualifient explicitement le ranking comme « hors périmètre » et placent la ranking policy dans une future Search Experience 5.5A.

`SearchFacetPolicy` fournit un catalogue admissible et un ordre canonique, mais aucun assembler productif ne détermine quelles facettes doivent être produites ni leurs valeurs owners.

## Fail-fast

Ni zéro, ni 1, ni 100, ni 500, ni 600 ne peut être retenu. Aucune liste vide de facettes ne peut être utilisée comme raccourci RC2.

## Autorité préalable

Une décision produit Search Ranking Policy est requise. Elle doit décider au minimum la finalité du rang, le sens du tri, la baseline, les signaux autorisés et l'absence ou présence de mécanismes commerciaux. Une décision parallèle doit fermer le catalogue de facettes effectivement matérialisées.

## Verdict

**NO GO PROPOSÉ — PUBLIC SEARCH RANKING AND FACET SOURCE AUTHORITY 01**
