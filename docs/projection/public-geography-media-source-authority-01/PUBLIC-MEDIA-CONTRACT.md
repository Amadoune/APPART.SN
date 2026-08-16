# Public Media contract

Le contrat consommé est `PublicMediaDecisionReader::read(mediaCollectionId): PublicMediaReadResult`.

Résultats fermés : `Found`, `Missing`, `Corrupted`. `Found` porte la collection, une révision positive, une couverture optionnelle et une galerie ordonnée; chaque item expose mediaId, URL publique et variantes. Le store est `public_media.decisions`; le reader est `PostgreSqlPublicMediaReader`.
