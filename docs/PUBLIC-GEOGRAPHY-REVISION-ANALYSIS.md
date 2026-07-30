# Sprint 3.7A — analyse d’architecture

Public Geography doit fournir une version explicite au watermark public sans faire dépendre la projection d’une horloge. La fondation 3.7A définit ce protocole dans la couche Application et ne modifie pas le Domain Geography certifié.

La révision stable contient trois preuves : une version publique positive, le checksum SHA-256 du payload public canonique et une clé de causalité. La version provient d’une séquence source explicite. Le checksum rend le contenu reproductible ; la causalité explique la mutation à l’origine de la révision.

La stratégie de versionnement construit la révision uniquement à partir de ces entrées. Elle ne consulte ni horloge, ni base de données, ni Runtime. À entrées identiques, la révision est identique.

La politique de stabilité compare une Candidate à la révision stable :

- absence de stable : promotion initiale ;
- version supérieure : avancement promotable ;
- version identique et même fait : déjà stable ;
- version inférieure : obsolète ;
- version identique mais contenu ou causalité différents : divergence.

Une divergence ne peut jamais être promue silencieusement. La fondation expose uniquement un contrat de lecture des révisions stables. Son implémentation de production appartient à un sprint ultérieur de sources Runtime.
