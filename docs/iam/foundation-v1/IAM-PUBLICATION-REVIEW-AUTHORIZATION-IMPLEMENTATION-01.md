# IAM Publication Review Authorization Implementation 01

## Implémentation

L'autorité IdentityAccess qualifiée est matérialisée sans nouvelle décision :

- `PublicationReviewCapabilityV1` contient exactement les quatre capacités certifiées ;
- `PublicationReviewAuthorizationStatus` contient exclusivement `Allowed`, `Denied` et `DependencyUnavailable` ;
- `PublicationReviewAuthorizationResult` n'expose que le statut ;
- `PublicationReviewAuthorizationReaderV1` reçoit `AccountId`, capacité et `observedAt` ;
- `OwnerPublicationReviewAuthorizationReaderV1` relit uniquement l'`AccountRegistry` IAM ;
- `PublicationReviewAuthorizationServiceProvider` fournit un binding nominatif singleton/lazy.

## Réduction fail-closed

| État IAM | Résultat |
|---|---|
| compte actif avec rôle `publication_reviewer` | `Allowed` |
| compte absent, suspendu ou sans rôle dédié | `Denied` |
| source indisponible ou identité retournée incohérente | `DependencyUnavailable` |

Le rôle `moderator` n'accorde aucune capacité Publication Review. Le Reader n'accède jamais à PublicationReview, Listing, Property, Media, Projection ou Search.

Le transport HTTP/UI reste hors périmètre : le type `AccountId` garantit que la future composition reçoit l'identité canonique établie par la session, sans accepter une identité client.
