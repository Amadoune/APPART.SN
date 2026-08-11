# Media Binary Storage Authority 01 — Implementation Evidence

## Composants

- port `MediaBinaryObjectStore` ;
- contrat Runtime `MediaBinaryStorageAuthorityV1` ;
- requête et résultats fermés de stockage ;
- `DeterministicMediaBinaryStorageAuthority` ;
- adaptateur `LaravelFilesystemMediaBinaryObjectStore` ;
- `MediaBinaryStorageServiceProvider` ;
- disque Laravel privé `media` ;
- accès `binary()` ajouté à `MediaIngestionRuntimeV1`.

## Garanties

- flux PHP réel accepté par le port ;
- octets persistés puis relus pour vérification ;
- checksum SHA-256 stable ;
- clé owner-scoped déterministe ;
- replay identique convergent ;
- divergence de contenu refusée sans écrasement ;
- métadonnées et checksum conservés dans `MediaAssetStore` ;
- compensation du blob nouvellement créé lorsque l'écriture d'asset échoue ;
- aucun SQL de fichier, second stockage, chemin absolu ou payload public.

## Preuve de démonstration

Le test utilise un flux contenant des octets JPEG de démonstration identifiés `photo1.jpg`. Il vérifie l'existence du blob, sa relecture octet pour octet, son checksum, sa taille, le replay et l'état durable `quarantined`.

La campagne PostgreSQL confirme que l'asset est reconstructible avec owner, storage key et content checksum, tandis que `media_attachment_intents` reste vide.
