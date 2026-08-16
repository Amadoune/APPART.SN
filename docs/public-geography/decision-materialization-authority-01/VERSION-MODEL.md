# Version model

Le modèle existant exige une `sourceSequence` positive : absence → Initial/Applied; séquence supérieure → Advance/Applied; séquence égale et même checksum/causalité → AlreadyApplied; inférieure → RejectedObsolete; égale différente → Divergent.

L'`aggregate_version` du seul Place feuille ne domine pas une modification publique d'un parent. Aucune séquence composite autoritative n'existe. La version ne peut donc pas être fermée avant l'autorité préalable de représentation publique.

## Completion 01

Le vecteur root→leaf est la révision autoritative. Sa somme positive et vérifiée est le watermark scalaire du writer; le checksum porte le payload incluant le vecteur. La somme ne remplace jamais le vecteur. RC2 : `[1,1,1]`, watermark 3.
