# Evolution Policy

La matrice est normative et codée par version, jamais pilotée silencieusement par configuration ou données runtime.

Toute évolution exige :

1. une nouvelle décision d’autorité RealEstateCatalog motivant le changement ;
2. une matrice exhaustive incluant chaque `PlaceType` ;
3. une analyse de compatibilité RegisterProperty, ChangeAddress, Authoring et Promotion ;
4. des tests unitaires et de composition mis à jour ;
5. un changelog/version de policy si le comportement des types existants change.

L’ajout futur d’un `PlaceType` Geography doit provoquer un échec explicite tant qu’aucun verdict RealEstateCatalog n’a été certifié. Aucun `default` permissif n’est autorisé.
