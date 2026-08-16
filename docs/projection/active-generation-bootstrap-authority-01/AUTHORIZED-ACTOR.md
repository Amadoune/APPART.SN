# Authorized Actor

L'acteur autorisé est le **deployment operator** qui possède déjà l'autorité d'exécuter les étapes de déploiement et d'accéder à la configuration de l'environnement. Ce n'est ni un utilisateur IAM, ni un reviewer, ni un Listing owner. Aucun nouveau rôle IAM n'est nécessaire: la commande est une surface CLI d'exploitation hors HTTP et doit consigner l'identité opérateur fournie par le système de déploiement.
