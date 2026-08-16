# Representation payload

Structure canonique V1 :

```text
schemaVersion = public-geography-place-representation-v1
terminalPlaceId
locality = officialName du terminal
breadcrumb = items root→leaf
revisionVector = [{placeId, aggregateVersion}] root→leaf
```

L'ordre des clés et listes est fixe pour le checksum. Aucun enrichissement Consumer n'entre dans ce payload.
