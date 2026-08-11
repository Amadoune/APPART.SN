# Boundary Audit

## Informations disponibles après inspection

Le cookie normatif a la forme `s1.<session-id-uuid>.<secret-base64url>`. Après parsing, lookup owner-scoped et réduction F1, l'inspection dispose de :

- `SessionId`, issu de l'enveloppe opaque ;
- `AccountId`, issu du snapshot Session relu ;
- verdict de validité ;
- état et version persistés, utilisés par la réduction mais inutiles dans le contexte transporté ;
- secret présenté, consommé par la vérification et interdit de propagation.

La surface actuelle `IdentityAccessSessionInspection` ne conserve que `valid` et `AccountId`. C'est à cet endroit précis que `SessionId` est perdu.

## Besoins par opération

### Inspection

Elle requiert `SessionId` pour le lookup, le secret uniquement pendant la vérification, puis `AccountId` pour établir l'owner. Après succès, seule la paire `AccountId + SessionId` doit survivre.

### Logout

Il requiert `SessionId` pour révoquer exactement la session authentifiée et `AccountId` pour vérifier que la row relue appartient à l'owner du contexte.

### Rotation

Elle requiert la même paire. Le Runtime relit état et version depuis le store ; il ne les reçoit pas du transport. L'optimistic locking désigne un seul gagnant.

## Frontières

Autorisé ultérieurement : types Application IAM, inspection typée, attribut interne middleware, commande typée.

Interdit : transmettre le secret au Controller, sérialiser le contexte, accepter un SessionId du payload client, utiliser un état global mutable, rechercher une session par AccountId seul ou modifier la Security Policy.
