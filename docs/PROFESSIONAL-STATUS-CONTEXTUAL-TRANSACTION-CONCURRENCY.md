# Professional Status Contextual Transaction and Concurrency

Le repository réutilise toute transaction externe et ouvre une transaction locale uniquement lorsqu'aucune transaction n'existe. Un verrou advisory transactionnel déterministe est acquis par `ProfessionalStatusId` avant lecture ou écriture.

Transition et contexte sont validés ou annulés ensemble. Deux commandes identiques concurrentes convergent vers `Applied` et `AlreadyApplied`, avec exactement deux lignes historiques — initialisation comprise — et une ligne contextuelle. Aucun état partiel n'est observable.
