# V1/V2 compatibility evidence

- Un payload sans `schemaVersion` suit exactement le reader V1 et conserve `label/url`.
- `public-geography-place-representation-v2` suit exclusivement le reader V2.
- Toute autre version est rejetée comme corrompue.
- Les DTO Projection/read model conservent les champs V1 et ajoutent `breadcrumbSchemaVersion` et `geographyBreadcrumb`.
- Le renderer conserve les liens V1 historiques et rend V2 en texte.

Les tests mapper et page couvrent les deux chemins sans réécriture des données historiques.
