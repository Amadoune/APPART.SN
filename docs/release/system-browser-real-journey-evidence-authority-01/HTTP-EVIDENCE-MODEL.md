# Modèle de preuve HTTP

Pour chaque mutation ou lecture critique, consigner :

- étape et horodatage opérateur ;
- URL et méthode ;
- statut HTTP ;
- actor AccountId corroboré, jamais fourni comme autorité client ;
- PropertyId, ListingId ou queueItemId concernés ;
- `commandId`, `occurredAt`, expectedVersion et version résultante lorsque présents ;
- résultat applicatif fermé ;
- référence à la capture Network ou au manifest.

Les headers CSRF peuvent être attestés sans valeur. Les cookies sont documentés uniquement par leur nom et leurs attributs. Les credentials, valeurs de cookie, tokens CSRF et secrets de session sont exclus.
