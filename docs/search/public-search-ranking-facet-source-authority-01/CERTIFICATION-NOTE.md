# Certification Note

## Verdict

**NO GO PROPOSÉ**

## Cause racine

`SearchRank` ne possède aucune sémantique normative productive : ni objectif, ni direction de tri, ni valeur neutre, ni formule, ni signaux autorisés. Les constantes historiques sont exclusivement des fixtures ou données locales.

La politique de facettes ferme la forme et le catalogue de clés, mais aucune autorité productive ne décide quelles valeurs matérialiser depuis Property, Listing, Media et Geography.

## Conséquence

Le chemin normal et le catch-up RC2 ne peuvent produire une `SearchDecision` sans inventer une règle métier. Search, son implémentation et l'Iteration suivante restent fermées.

## Prérequis de réouverture

Une décision produit explicite doit qualifier le classement et la matérialisation des facettes. Aucun développement, migration, staging, commit ou tag n'est autorisé par cette certification.
