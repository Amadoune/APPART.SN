# Readiness handoff

`CertifiedPublicListingProjectionSource` lit la décision par placeId et expose `revision->watermarkVersion()`. `Found` avec une version positive remplit `publicGeographyVersion`.

La composante Geography passe alors; Media manquante conserve `missing_public_media_version`. Bootstrap ne doit pas être rouvert avant les deux sources Found.
