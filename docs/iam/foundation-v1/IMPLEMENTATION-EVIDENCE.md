# Publication Review Authorization — Implementation Evidence

## Preuves fonctionnelles

- Les quatre cas du catalogue fermé rendent `Allowed` pour un compte actif portant `publication_reviewer`.
- `moderator`, compte absent, compte suspendu et compte sans rôle rendent `Denied`.
- Une exception du registre ou une identité incohérente rend `DependencyUnavailable`.
- Aucun résultat ou diagnostic supplémentaire n'est exposé.

## Preuves de composition

- Le Provider lie nominativement `PublicationReviewAuthorizationReaderV1` à l'implémentation owner IdentityAccess.
- La résolution est lazy et retourne le même singleton.
- Aucun alias implicite n'est déclaré.

## Preuves de frontière

- Aucun SQL dans Application.
- Aucune dépendance framework dans le Reader.
- Aucune dépendance vers PublicationReview, Listing Lifecycle, Property, Media ou Search.
- Aucune modification Runtime IAM, Session, Cookie ou PublicationReview.
- Aucune migration : PostgreSQL est `NOT_APPLICABLE` pour cette lecture via l'`AccountRegistry` certifié.
