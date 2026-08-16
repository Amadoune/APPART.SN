# Self-Identity Problem

Les trois contrôles intégrés au tree RC2 exigent encore :

- la base `058719f8aa154466056299b8c26bd7d51f944127` ;
- le tag `phase-5.9-baseline-candidate-r5`.

Les modifier change le tree RC2. Le tag RC2 existant ne peut donc pas être réaligné sans violer son immutabilité.

Le problème n'est pas qu'une candidate ignore son propre SHA : Git permet au tag créé après le commit de porter cette liaison. Le paradoxe apparaîtrait seulement si le SHA ou le tree futur devait être inscrit dans le contenu versionné. Le modèle retenu l'interdit.
