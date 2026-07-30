# Phase 5.2B — Persistence Strategy

## Principe

La future persistence sera additive, owner-scoped et postérieure à 057. Aucun
schéma existant n’est modifié.

## Stores candidats

| Owner | Données minimales |
|---|---|
| Upload | uploadId, actor scope, intentId, checksum d’intention, limites, expiration, état et version |
| Asset | assetId, checksum réel, type détecté, taille, dimensions, object key opaque, safety state et version |
| Processing | assetId, recipeVersion, attempt, lease, résultat, diagnostics internes et variantes |
| Quota | scope, limite versionnée, réservation, consommation, expiration et version |

## Garanties

- optimistic locking ou sérialisation explicite par owner ;
- unicité déterministe des intentIds et checksums ;
- leases expirables pour workers concurrents ;
- aucune FK cross-domain et aucune cascade ;
- transaction locale par owner ; saga compensée entre binaire et métadonnées ;
- aucun commit DB ne prétend rendre atomique un object store externe ;
- reconciliation obligatoire des objets orphelins et lignes sans objet ;
- chiffrement, rétention et purge gouvernés par classe de données.

## Rétention candidate

Les durées exactes restent à certifier. Le modèle distingue au minimum :
uploads incomplets, quarantaine, asset canonique rattaché, variantes
reconstruisibles, asset abandonné et preuve minimale de suppression. La purge
physique est différée, idempotente et vérifiable.
