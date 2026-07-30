# Search Projection — Sprint 3.2

## Rôle

`SearchListingProjection` est la première fiche de lecture destinée à la recherche publique. C'est un objet `readonly` composé uniquement de scalaires et de dates immutables. Il ne possède aucune méthode métier et ne peut modifier aucune source.

`SearchListingProjectionBuilder` lit les APIs publiques de `Property`, `MediaCollection` et `Listing`. Il produit une fiche ou `null` lorsque les faits sources ne permettent pas une fiche publique cohérente.

## Reconstruction

La reconstruction ne demande aucun état antérieur de projection :

1. charger les trois Aggregates depuis leurs repositories certifiés;
2. passer leurs instantanés au builder;
3. remplacer intégralement l'ancienne fiche éventuelle par le résultat;
4. supprimer la fiche éventuelle lorsque le résultat est `null`.

À sources identiques, le résultat est identique. Aucun identifiant, horodatage ou classement n'est généré par le builder.

## Dépendances

- Property fournit identité, géographie, type, surface, pièces et statut d'archivage;
- MediaCollection fournit identité, rattachement Property et principal actif;
- Listing fournit identité, rattachement Property, statut, historique de révisions et expiration.

La dépendance est à sens unique : le builder connaît les Aggregates; aucun module ni Aggregate ne connaît `App\Projections`. Aucun Repository, Registry, SQL, migration, Dispatcher, Outbox ou Unit of Work n'est ajouté.

## Limites

- pas de ville ou quartier tant que Geography ne fournit pas les libellés correspondants;
- pas d'URL ou de variante média tant qu'aucune source publique ne les expose;
- pas de titre, description ou prix, absents des trois Aggregates audités;
- pas de score, rang ou facette calculée;
- pas de stockage ni de mise à jour événementielle dans ce sprint;
- aucune garantie de fraîcheur au-delà des instantanés fournis au builder.

La projection ne remplace jamais une revalidation métier. Publication, archivage, principal média et expiration restent exclusivement décidés par leurs Aggregates propriétaires.
