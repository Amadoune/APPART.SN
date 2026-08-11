# Implementation Evidence

## Provisioning

- le principal a été créé par `RegisterAccount` ;
- le `PasswordHash` a été produit par `CredentialHashAuthorityV1` ;
- le rôle `publication_reviewer` a été affecté par `GrantRole` ;
- la relecture détachée par `AccountRegistry` confirme le rôle actif et la version persistée ;
- aucun fichier applicatif ou contrat IAM n'a été modifié.

## Autorisation

`PublicationReviewAuthorizationReaderV1` retourne `Allowed` pour :

- `read_publication_review_queue` ;
- `claim_publication_review` ;
- `begin_publication_review` ;
- `approve_publication`.

## Transport HTTPS

Un login réel a été effectué sur `POST https://appart.test/api/identity-access/login` avec CSRF et `Idempotency-Key`. La réponse est HTTP 200, `status=succeeded`. Le cookie retourné est `__Host-appart_session`, `Secure`, `HttpOnly`, `SameSite=Strict`. La même session obtient HTTP 200 sur `GET /publication-review`.

Les fichiers temporaires contenant payload et cookie ont été supprimés immédiatement après la preuve. Le credential clair n'est pas conservé dans les livrables.
