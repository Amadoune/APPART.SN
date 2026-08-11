# APPART.TEST Local Publication Reviewer Principal Provisioning 01

## Périmètre

Le chantier provisionne un principal IAM strictement local destiné à la démonstration P08. Il ne modifie ni les politiques IAM, ni le Runtime, ni les contrats, ni le code produit.

## Audit des autorités

| Autorité | Qualification |
|---|---|
| principal F3 | absent du registre local au moment de l'audit ; aucune réinitialisation tentée |
| `CredentialHashAuthorityV1` | autorité certifiée de production du hash Argon2id |
| `RegisterAccount` | use case certifié de création d'un Account via `AccountRegistry` |
| `AccountRegistry` | port owner-scoped lié au registre PostgreSQL certifié |
| `GrantRole` | use case certifié d'affectation avec sauvegarde optimistic-lock |
| `PublicationReviewAuthorizationReaderV1` | reader fail-closed certifié des quatre capacités Publication Review |

## Principal local

- AccountId : `d8361225-709e-4f96-89c4-0995ee40d628`
- identifiant : `p08.reviewer@appart.test`
- rôle actif : `publication_reviewer`
- environnement : `local`

Le credential clair n'est pas écrit dans le repository. Son hash a été produit exclusivement par `CredentialHashAuthorityV1` puis persisté par `RegisterAccount` et `AccountRegistry`.

## Chaîne exécutée

`credential local → CredentialHashAuthorityV1 → RegisterAccount → AccountRegistry → GrantRole(publication_reviewer) → login HTTPS → PublicationReviewAuthorizationReaderV1`

Aucun SQL direct, hash manuel, cookie injecté, session artificielle, fixture ou mutation manuelle du registre n'a été utilisé.
