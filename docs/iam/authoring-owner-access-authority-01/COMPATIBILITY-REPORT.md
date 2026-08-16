# Rapport de compatibilité

La décision ne modifie aucun contrat, route, middleware, rôle, capability, Account, Session, Authoring Runtime ou modèle de ressource.

Elle rend explicite la composition existante : session IAM pour l’accès, AccountId de session pour l’actor, ownership/délégation pour les ressources.

Les rôles spécialisés Publication Review et Moderation restent isolés. Le principal reviewer existant n’est ni modifié ni réutilisé. Les Accounts historiques sans rôle conservent leur accès Authoring lorsqu’ils disposent d’une session valide.
