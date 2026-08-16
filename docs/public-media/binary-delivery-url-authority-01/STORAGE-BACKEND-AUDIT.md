# Storage backend audit

Le binding productif utilise `LaravelFilesystemMediaBinaryObjectStore` sur le disk Laravel `media`.

- driver courant : local filesystem;
- racine locale : `storage/app/media`;
- visibilité : private;
- écritures explicitement private;
- accès : `exists/get/put/delete`, pas d'URL publique;
- local RC2 : `APP_URL=https://appart.test`;
- aucun backend CDN ou S3 Media actif démontré.

Le disk `public` existe dans Laravel mais n'est pas le disk Media et ne constitue pas une voie autorisée.
