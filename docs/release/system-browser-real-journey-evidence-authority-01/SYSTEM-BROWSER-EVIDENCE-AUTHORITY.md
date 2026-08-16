# System Browser Real-Journey Evidence Authority 01

## Décision

Chrome système local est une surface probatoire recevable pour RC2 lorsque le navigateur contrôlable est indisponible pour une cause externe certifiée.

RC2 exige la preuve d'un parcours HTTP réel, d'une session IAM réelle, des états UI, des identités de commande et des états autoritatifs persistés. Aucun texte normatif audité n'impose que ces faits soient produits par une API d'automatisation. L'automatisation est un moyen historique de collecte, pas une autorité produit.

La preuve fermée combine :

`UI et HTTP observés dans Chrome système + identités de commande conservées + corroboration PostgreSQL par les stores autoritatifs`.

Les interactions manuelles sont recevables si elles utilisent exclusivement les formulaires, boutons et endpoints réels. Le fail-fast, les frontières IAM et l'interdiction de Search restent inchangés.

Cette Authority ne réalise aucun Login, ne crée aucun compte et ne reprend pas Iteration 11.
