# Exigences d’état Account

## Login

La source Credential relit le compte et marque indisponible un compte suspendu. Le verifier refuse alors l’authentification. Un credential correct sur un compte non suspendu peut produire une session.

La vérification email ou téléphone n’est pas interrogée par le Login HTTP actuel. Aucun rôle n’est interrogé.

## Authoring

Authoring ne relit pas directement l’Aggregate Account. Son autorité est une session IAM que `inspectSession()` juge valide. Authentication success et authorization spécialisée demeurent distinctes : Authoring n’ajoute aucune autorisation spécialisée au-delà de la session et de l’ownership ressource.
