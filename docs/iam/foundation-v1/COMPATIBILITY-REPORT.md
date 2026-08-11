# Publication Review Authorization — Compatibility Report

| Surface | Effet de la décision |
|---|---|
| IAM Foundation F1–F3 | aucune modification ; nouvelle autorité future additive |
| Session IAM | inchangée ; transporte déjà l'AccountId authentifié |
| Report Moderation | rôle, Reader et capacités inchangés |
| PublicationReview | consommera uniquement la décision fermée ; aucune lecture IAM directe |
| Listing Lifecycle | reçoit mécaniquement l'actor déjà autorisé |
| Projection / Search | aucun accès IAM, aucun changement |
| Listings historiques | aucun backfill ni changement |

## Transport normatif

1. `RequireIdentityAccessSession` établit l'AccountId authentifié.
2. La frontière P08 sélectionne statiquement la capacité correspondant à l'opération.
3. Le Reader IAM rend `Allowed`, `Denied` ou `DependencyUnavailable`.
4. Seul `Allowed` permet l'appel PublicationReview.
5. L'AccountId de session, jamais une valeur client, devient l'actor.

Il n'existe aucun partage implicite entre `publication_reviewer` et `moderator`. Une affectation éventuelle des deux rôles au même compte resterait deux décisions IAM distinctes et auditables.
