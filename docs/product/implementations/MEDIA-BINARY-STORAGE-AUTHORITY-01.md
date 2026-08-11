# Media Binary Storage Authority 01

## Objectif

Media Runtime possède désormais une autorité capable de recevoir un flux binaire réel, de persister ses octets sur un disque privé, de calculer son SHA-256 et de matérialiser un `MediaAsset` durable en quarantaine.

## Chaîne certifiable

`MediaIngestionRuntimeV1::binary()`
→ `MediaBinaryStorageAuthorityV1`
→ `MediaBinaryObjectStore`
→ disque Laravel privé `media`
→ `MediaAssetStore`
→ état `quarantined`

## Identité et stockage

La clé est déterministe et owner-scoped :

`owners/{ownerId}/assets/{assetId}`

Le root physique provient exclusivement de `config/filesystems.php` (`storage_path('app/media')`). Aucun chemin absolu n'est codé dans l'Application ou l'adaptateur.

## Sémantique

- premier contenu pour une identité owner/asset : `Applied` ;
- replay du même contenu : `AlreadyApplied` ;
- contenu différent pour la même identité : `DivergentContent` ;
- indisponibilité du disque ou de l'asset store : `DependencyUnavailable`.

Le SHA-256 est calculé sur les octets effectivement reçus puis vérifié après relecture du stockage.

## Frontière

L'asset durable est créé en état `quarantined`. Ce chantier ne réalise aucune validation métier, transition `ready`, collection, attachement, façade HTTP ou publication.
