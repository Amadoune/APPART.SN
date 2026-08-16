# RC2-01 — Media 503 Correction

## Incident qualifié

La campagne RC1-B6-R1 atteignait un fichier PNG réel, un `FileList` peuplé et un multipart réel, puis recevait HTTP 503 sur `POST /api/authoring/properties/{propertyId}/media`.

L'instrumentation RC2 a reproduit le défaut sur la surface HTTP réelle et identifié l'exception exacte :

`ValueError: Path must not be empty`

Elle provenait de `fopen($image->getRealPath(), 'rb')`. Sous le transport Apache/FastCGI local, l'`UploadedFile` était valide, mais `getRealPath()` ne fournissait pas de chemin exploitable. Le Runtime Media n'était pas appelé.

## Correction minimale

Le contrôleur ouvre désormais le fichier temporaire avec `UploadedFile::getPathname()`, qui est le pathname transporté par l'objet uploadé. Aucune règle Media, Property, IAM, Listing, Search ou Projection n'est modifiée.

Une journalisation structurée et sans donnée métier distingue désormais :

- échec d'ouverture du flux ;
- résultat Runtime indisponible ;
- exception non capturée par une réduction plus précise.

## Résultat

Le rejeu navigateur réel retourne HTTP 201 pour le même POST multipart. L'upload atteint Binary Storage, Readiness et Attachment, puis la photo apparaît dans la Preview.

La campagne fail-fast rencontre ensuite HTTP 503 sur `POST /api/public-authoring/v1/submit-listing`. Cette divergence est postérieure à la correction Media et hors périmètre RC2-01 ; elle n'est ni analysée ni corrigée ici.
