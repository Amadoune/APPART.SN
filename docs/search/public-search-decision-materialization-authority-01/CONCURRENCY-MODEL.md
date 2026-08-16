# Concurrency Model

Le writer couvre déjà la concurrence de persistance par transaction, verrou et upsert conditionnel :

- un seul résultat supérieur peut devenir courant ;
- une version inférieure devient `RejectedObsolete` ;
- une même version divergente devient `Divergent` ;
- une même version identique devient `AlreadyApplied`.

Ce mécanisme ne résout pas l'allocation concurrente d'une nouvelle version, le snapshot cohérent des trois sources ni la reprise après crash entre lecture et écriture. Sans autorité de version et source assembler, le modèle applicatif concurrent reste incomplet.

Aucune transaction distribuée avec Listing, Property, Media ou Projection n'est recevable.

## Completion 01

Deux workers sur les mêmes sources proposent même identité, version et checksum : un `Applied`, l'autre `AlreadyApplied`. Un worker ancien relisant une décision dominante retourne `RejectedObsolete`. Deux candidates incomparables ou divergentes échouent fermé en `Divergent` et sont rejouables après nouvelle lecture.
