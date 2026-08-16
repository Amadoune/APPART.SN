# Contrôle de sécurité

| Invariant | Résultat |
|---|---|
| credential hashé par CredentialHashAuthorityV1 | PASS |
| création par RegisterAccount et AccountRegistry | PASS |
| aucun GrantRole | PASS |
| zéro rôle sur le nouveau compte | PASS |
| reviewer non réutilisé et non modifié | PASS |
| aucun SQL IAM direct | PASS |
| aucun cookie/session injecté | PASS |
| AccountId issu de la session | PASS |
| credential clair absent du repository et des documents | PASS |
| artefacts opérateur temporaires supprimés après Login | PASS |
| aucun compte existant modifié | PASS |
| aucun staging, commit ou tag | PASS |

Le credential clair n’est pas reproduit dans les preuves. Seul son hash Argon2id autoritatif est persistant.
