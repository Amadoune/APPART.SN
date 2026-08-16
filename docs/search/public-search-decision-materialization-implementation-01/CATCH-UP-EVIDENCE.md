# Catch-up Evidence

La commande productive `appart:search:materialize {listingId}` délègue à `CatchUpPublicSearchDecisionV1`. Elle n’introduit ni règle métier ni écriture parallèle.

Exécution RC2 sur `979cd5aa-ced1-48a1-8adf-8b29c843a0c2` :

- première exécution : `applied` ;
- replay : `already_applied`.

Le catch-up a été exécuté uniquement après le passage des validations ciblées.
