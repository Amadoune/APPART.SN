# Version Model

- premier snapshot : version 1 ;
- mêmes révisions et même payload : version stable, writer `AlreadyApplied` ;
- ensemble de sources dominant : version courante + 1 ;
- ensemble entièrement inférieur : `RejectedObsolete` ;
- versions croisées ou même révision logique avec contenu différent : `Divergent` ;
- aucun saut fondé sur l’horloge.

Ces règles composent les résultats existants du writer sans les modifier.
