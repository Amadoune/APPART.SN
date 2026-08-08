# Risk Register

| Risque | Maîtrise | Résiduel |
|---|---|---|
| création artificielle d'un état pour un Reader sans source | blocage explicite des trois chaînes incomplètes | faible |
| contournement du port par PostgreSQL ou mapper | dépendance exclusive au port Owner Source | faible |
| disponibilité Runtime interprétée comme donnée publique | Runtime explicitement exclu | faible |
| fallback ou agrégation entre streams | réduction indépendante, bijective et homonyme | faible |
| fuite de Revision State ou donnée sensible | sortie limitée à status et observedAt du contrat V1 | faible |
| ouverture implicite d'une Foundation | audit documentaire sans code ni test | faible |
