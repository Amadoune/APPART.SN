# Séparation owner / reviewer

Deux principals IAM distincts sont obligatoires :

- Authoring : compte actif sans rôle, owner uniquement des ressources qu'il crée ;
- Publication Review : compte actif portant exclusivement le rôle certifié `publication_reviewer` nécessaire au parcours.

Méthode recommandée : deux profils Chrome RC2 dédiés, propres et séparés. Elle permet de conserver deux sessions réelles sans copie de cookie et réduit le risque de confusion d'actor.

Un logout/login séquentiel dans un seul profil reste techniquement recevable mais moins auditabile. L'incognito n'est recevable que si ses contextes sont séparés et ses preuves identifiables sans partage de storage.

Si le credential reviewer certifié n'est plus disponible, un nouveau reviewer local distinct peut être créé avant le parcours par la chaîne déjà certifiée : `CredentialHashAuthorityV1 → RegisterAccount → AccountRegistry → GrantRole(publication_reviewer)`. Aucun rôle reviewer n'est accordé au principal Authoring.
