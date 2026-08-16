# Sémantique DivergentCommand

`DivergentCommand` est retourné lorsque :

- un commandId ledger existe avec une identité/checksum différente ;
- aucun ledger identique ne couvre la commande et l’Aggregate existant diffère d’au moins une identité ou un fait canonique attendu ;
- l’AddressId existant diffère du résultat F2, même si PlaceId et AddressLine convergent.

Ce résultat n’entraîne aucune compensation, réécriture, seconde Property, création de ledger de succès ou poursuite Submit.
