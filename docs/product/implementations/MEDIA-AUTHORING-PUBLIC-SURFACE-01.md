# Media Authoring Public Surface 01

## Objectif audité

Permettre à un propriétaire authentifié de téléverser une image réelle, de la rattacher à son brouillon owner-scoped puis de relire cette collection, exclusivement via les capacités Media certifiées.

## Chaîne requise

`Session IAM → brouillon owner-scoped → upload binaire → Media Ingestion → asset ready → MediaCollection → rattachement → relecture`

## État réel des frontières

| Besoin | Surface observée | Statut |
|---|---|---|
| Ownership du brouillon | `PropertyAuthoringStore` et `ListingOwnershipStore` | AVAILABLE |
| États d'upload | `MediaUploadStore` via `MediaIngestionRuntimeV1` | PARTIAL — métadonnées uniquement |
| Stockage des octets | aucun port Media | MISSING |
| Asset | `MediaAssetStore` | PARTIAL — état durable, aucun ingest binaire |
| Création de collection | `CreateMediaCollection` | PARTIAL — exige un `PropertyCatalog` Media |
| Property Authoring reconnu par Media | aucun adaptateur `PropertyAuthoringStore → Media PropertyCatalog` | MISSING |
| Ajout à la collection | `AddMedia` | AVAILABLE pour un média déjà qualifié |
| Attachement idempotent | contrat `AttachReadyMediaAssetV1` et intent store 059 | PARTIAL — aucune implémentation/binding |
| Relecture de collection | `MediaCollectionRegistry` | AVAILABLE |
| Suppression | lifecycle Remove/Archive certifié | AVAILABLE pour un média déjà rattaché |

## Cause racine

Le repository possède les briques de persistance d'états et le modèle `MediaCollection`, mais pas la composition qui transforme un fichier reçu en asset Media prêt et owner-scoped. Une façade HTTP ne peut pas combler cette absence sans créer un stockage binaire ou écrire directement dans les stores.

## Décision

La surface n'est pas matérialisée. Toute implémentation partielle aurait produit une référence de fichier sans fichier réel, un asset artificiellement marqué ready, ou une collection liée sans preuve d'ownership — trois violations explicites du mandat.
