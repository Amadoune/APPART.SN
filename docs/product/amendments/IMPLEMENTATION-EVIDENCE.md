# Public Search Filterable Read Model 01 — Implementation Evidence

## Statut

`NOT_IMPLEMENTED`.

## Motif

Le contrat filtrable ne peut pas être exhaustif avec les sources autorisées. Les filtres ville et type sont techniquement dérivables de la projection existante, mais la transaction n'y existe pas.

## Options rejetées

- Relire `ListingDraftState` ou `PostgreSqlListingDraftStore` pendant une recherche : relecture Authoring interdite.
- Ajouter une jointure vers les tables Authoring dans le reader : dépendance cross-boundary et nouvelle source implicite.
- Déduire Acheter/Louer du prix, du titre ou de l'UI : sémantique inventée.
- Ajouter une facette transaction directement à la donnée P02 : modification ponctuelle sans source publique qualifiée.
- Accepter le paramètre puis ne pas l'appliquer : résultat trompeur.
- Filtrer les résultats après `LIMIT` : pagination incorrecte et explicitement interdite.

## Intégrité

Aucun contrat, Query, Summary, Reader, Request, endpoint, projection, front office, provider, migration ou composant P02 n'a été modifié.
