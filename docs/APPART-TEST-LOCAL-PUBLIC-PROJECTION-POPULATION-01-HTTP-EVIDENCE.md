# APPART.TEST LOCAL PUBLIC PROJECTION POPULATION 01 — HTTP Evidence

## Collection publique

Requête observée :

`GET http://appart.test/api/public-search/results`

Résultat : HTTP 200 avec :

```json
{"status":"empty","items":[],"nextCursor":null}
```

Le endpoint et son binding sont fonctionnels. Le critère produit `status=available` avec au moins un item n'est pas satisfait.

## Fiche publique

Aucun `canonicalPath` réel n'étant disponible, aucune URL de fiche correspondante ne peut être construite ni certifiée HTTP 200. Cette preuve est `BLOCKED`, et non simulée.
