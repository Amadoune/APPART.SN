# Route Evidence

- Méthode : `GET`.
- Locator exact : `/media/{mediaId}/revisions/{assetVersion}`.
- `mediaId` : contrainte UUID et validation par l'identité Media.
- `assetVersion` : contrainte numérique et validation positive.
- Route publique : aucun middleware IAM, aucun login et aucun cookie requis.
- Contrôleur : `PublicMediaBinaryController`, limité à la réduction HTTP du résultat fermé.
