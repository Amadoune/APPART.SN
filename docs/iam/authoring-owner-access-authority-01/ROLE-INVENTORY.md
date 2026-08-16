# Inventaire des rôles IAM

| Rôle observé | Autorité productive | Consumer | Frontière |
|---|---|---|---|
| `publication_reviewer` | oui | `PublicationReviewAuthorizationReaderV1` | Publication Review |
| `moderator` | oui | `ModeratorAuthorizationReaderV1` | Report Moderation |
| `moderation_auditor` | oui | `ModeratorAuthorizationReaderV1` | audit Moderation |
| `particulier` | non démontrée | aucun consumer productif | convention de tests Domain |
| `manager` | non démontrée | aucun consumer productif | tests de persistance historique |

`GrantRole` est un use case générique ; il ne transforme pas toute chaîne valide en rôle autoritatif. Aucun rôle `owner`, `advertiser` ou `annonceur` n’est défini ou consommé.
