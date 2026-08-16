# RC2-01 — Certification Note

## Verdict

**NO GO PROPOSÉ**

La correction Media est démontrée : la cause exacte du 503 était l'usage de `UploadedFile::getRealPath()` dans le contexte FastCGI ; `getPathname()` restaure l'ouverture du flux. L'upload réel retourne HTTP 201, produit un asset ready, l'attache et alimente la Preview.

Le critère global RC2-01 exige néanmoins la chaîne jusqu'à la fiche publique. Le rejeu fail-fast s'arrête au premier défaut postérieur : `submit-listing` retourne HTTP 503. Cette cause est hors périmètre de la correction Media et n'a pas été modifiée.

RC1, le Runner et toutes les frontières interdites restent inchangés. Aucun staging, commit ou tag n'est effectué.
