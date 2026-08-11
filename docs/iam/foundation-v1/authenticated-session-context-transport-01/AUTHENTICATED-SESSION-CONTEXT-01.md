# Authenticated Session Context 01

## Décision

Le contexte interne minimal est :

`AuthenticatedSessionContext(AccountId, SessionId)`

La décision retient l'option **C — AccountId + SessionId**.

## Sémantique

- `AccountId` est l'owner authentifié établi par l'inspection ;
- `SessionId` est l'identité opaque de la session qui a présenté une preuve valide ;
- la paire n'accorde aucun droit par elle-même : le Runtime relit obligatoirement la Session et vérifie la concordance `session.accountId === context.accountId` avant toute mutation ;
- le contexte ne contient ni cookie, ni secret, ni HMAC, ni credential, ni policy state.

## Propriétés obligatoires

Le contexte est :

- immutable ;
- owner-scoped ;
- opaque hors IAM ;
- non sérialisable dans une réponse publique ;
- non persistant ;
- limité à la durée de la requête ;
- produit exclusivement après une inspection Session valide.

`SessionId` n'est pas un secret selon `SESSION-SECRET-POLICY-V1`. Il demeure néanmoins une identité interne et n'est pas ajouté aux payloads HTTP.

## Transport qualifié

La future composition pourra suivre cette chaîne sans couplage HTTP dans le Runtime :

1. `inspectSession(cookie, observedAt)` valide le secret et retourne une inspection contenant le contexte typé ;
2. le middleware transporte ce contexte comme attribut interne de requête ;
3. le Controller le place dans la commande applicative typée ;
4. le Runtime relit la Session par `SessionId`, vérifie son `AccountId`, puis applique réduction et optimistic locking.

La présente décision ne matérialise aucun de ces changements.
