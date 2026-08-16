# Matrice d’autorisation Authoring

| Principal | Session valide | Accès initial Authoring | Mutation d’une ressource existante |
|---|---:|---:|---|
| compte actif sans rôle | oui | autorisé | owner ou délégation requise |
| `publication_reviewer` | oui | techniquement autorisé | owner ou délégation requise |
| `moderator` | oui | techniquement autorisé | owner ou délégation requise |
| `moderation_auditor` | oui | techniquement autorisé | owner ou délégation requise |
| compte suspendu sans session valide | non | refusé | refusé |
| principal non authentifié | non | refusé | refusé |

Un rôle spécialisé n’accorde ni ne retire implicitement l’Authoring. La séparation RC2 impose néanmoins un principal owner distinct du reviewer comme contrainte de démonstration.
