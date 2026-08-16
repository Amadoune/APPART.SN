# Principal local de démonstration

## Forme normative

Option A retenue : **AUTHENTICATED PRINCIPAL SUFFICIENT**.

Le futur principal local RC2 doit être :

- un nouvel Account réservé à `appart.test` ;
- distinct du principal `publication_reviewer` ;
- non suspendu et authentifiable ;
- créé par CredentialHashAuthorityV1, RegisterAccount et AccountRegistry ;
- sans rôle owner, reviewer, moderator ou admin ;
- connecté par le Login HTTPS réel.

Son AccountId de session deviendra owner uniquement des ressources qu’il crée.
