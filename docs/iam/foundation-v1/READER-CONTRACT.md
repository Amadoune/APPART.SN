# Publication Review Authorization Reader — Contract Decision

## Contrat qualifié

Nom cible : `PublicationReviewAuthorizationReaderV1`.

Entrée minimale :

- `AccountId` canonique IAM ;
- `PublicationReviewCapabilityV1` appartenant au catalogue fermé ;
- `observedAt` UTC explicite.

Résultat fermé exclusivement à :

- `Allowed` ;
- `Denied` ;
- `DependencyUnavailable`.

## Sémantique

- `Allowed` : le compte possède la capacité demandée à `observedAt`.
- `Denied` : le compte existe mais la capacité n'est pas accordée, ou son état ne permet pas l'autorisation.
- `DependencyUnavailable` : l'autorité ne peut pas établir une décision fiable. Le consommateur refuse l'opération.

Le Reader ne retourne ni rôle, ni liste de capacités, ni raison interne, ni donnée métier, ni diagnostic. Il est read-only, owner-scoped IdentityAccess, framework-agnostic et fail-closed.

Cette spécification ne constitue pas un contrat PHP implémenté et n'ouvre ni Provider ni binding.
