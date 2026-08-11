# Login Identity Policy V1

## Catalogue fermé

Deux identifiers sont admis :

1. email ;
2. téléphone au format international E.164.

Username et AccountId sont exclus du login public V1. Aucun autre identifier n'est déduit.

## Classification et normalisation

- espaces extérieurs supprimés ; espaces intérieurs refusés hors séparateurs admis du téléphone ;
- présence d'un `@` : traitement exclusif comme email ;
- préfixe `+` sans `@` : traitement exclusif comme téléphone ;
- toute autre forme : rejet générique ;
- email : `trim`, minuscules Unicode, validation email, longueur maximale 254, conformément à `EmailAddress` ;
- téléphone : suppression de `space`, `.`, `-`, `(`, `)`, puis `+` suivi de 8 à 15 chiffres avec premier chiffre non nul, conformément à `PhoneNumber` ;
- aucun fallback d'un type vers l'autre.

## Résolution

Le resolver interne retourne seulement `Resolved(AccountId)`, `NotResolved` ou `DependencyUnavailable`. Il ne retourne jamais email, téléphone ou hash.

## Anti-énumération

`NotResolved` et mauvais credential suivent la même réduction publique, le même statut HTTP et une voie de vérification temporellement homogène. Pour un compte absent, le verifier exécute une vérification Argon2id contre un hash leurre V1 préconfiguré ; il ne crée ni ne persiste ce hash à chaque requête.

Aucun log ne contient l'identifier clair. Les métriques utilisent uniquement une catégorie fermée ou un fingerprint HMAC versionné.
