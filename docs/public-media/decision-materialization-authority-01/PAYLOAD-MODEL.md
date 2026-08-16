# Payload model

## Completion 01 — payload V2 gouvernant

Le payload productif contient `schemaVersion:2`, `mediaCollectionId`, `sourceRevision` checksumé et `items:[{mediaId,publicLocator,deliveryRevision,order,primary}]`. Le mapper exige exactement un primary et dérive cover/gallery pour les consumers V1. Un schema inconnu donne Corrupted. Le modèle V1 ci-dessous reste historique/lisible.

Payload existant exact :

```text
cover: null | {mediaId, url, variants:[{name,url}]}
gallery: [{mediaId, url, variants:[{name,url}]}]
```

L'ordre du tableau gallery est significatif et checksumé. Aucun status, path privé, checksum binaire, MIME ou dimension ne doit être ajouté sans contrat. Le payload RC2 ne peut être produit faute d'URL publique autoritative.
